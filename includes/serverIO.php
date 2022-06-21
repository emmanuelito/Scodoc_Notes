<?php
/******************************************/
/* serverIO.php
	Fonctions de communication vers le serveur Scodoc
*******************************************/
	$path = realpath($_SERVER['DOCUMENT_ROOT'] . '/..');
	include_once "$path/includes/default_config.php";
	include_once "$path/includes/annuaire.class.php";		// Class Annuaire
	
/**************************/
/* Configuration du CURL  */
/**************************/
	function CURL($url){
		global $Config;
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
		curl_setopt($ch, CURLOPT_FAILONERROR, true);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__.'/cookie.txt');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Serveur Scodoc non accéssible depuis le net, donc vérification impossible
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_REFERER, $_SERVER['SERVER_NAME'] . '/?passerelle=' . $Config->passerelle_version);

		$output = curl_exec($ch);
		curl_close($ch);
		return $output;    
	}

/**************************/
/* Ask_Scodoc() :

	Entrées :
		$url_query : [string] url de question à Scodoc - exemple : "Scolarite/Notes/etud_info"
		$dep - optionnel : [string] département, exemple : MMI. Si fonction url_query globale (sans département), laisser vide.
		$options - optionnel : tableau associatif des options à transmettre - exemple :
			[
				'formsemestre_id' => 'SEM8871',
				'format' => 'json'
			]

		Retour : [string] du résultat
****************************/
	function Ask_Scodoc($url_query, $dep = '', $options = []){
		global $Config;
		$path = realpath($_SERVER['DOCUMENT_ROOT'] . '/..');

		$login = [
			'__ac_name' => $Config->scodoc_login,
			'__ac_password' => $Config->scodoc_psw
		];
		
		$data = http_build_query(array_merge($login, $options));
		
		if($dep != ''){
			$dep = '/'.$dep;
		}

		return CURL($Config->scodoc_url . "$dep$url_query?$data");
	}

/*******************************/
/*******************************/
/*******************************/
/* getDepartmentSemesters()
	Liste des semestres actif d'un département
	Entrée :
		$dep : [string] département - exemple : 'MMI'
	Sortie :
		[
			{
				'titre' => 'titre du semestre',
				'semestre_id' => 'code semestre' // exemple : 'SEM8871'
			},
			etc.
		]
*******************************/
function getDepartmentSemesters($dep){
	$json = json_decode(
		Ask_Scodoc(
			'/Scolarite/Notes/formsemestre_list',
			$dep,
			[
				'format' => 'json'
			]
		)
	);
	$output = [];
	foreach($json as $value){
		if($value->etat == "1"){
			$output[] = [
				'titre' => $value->titre_num,
				'semestre_id' => $value->formsemestre_id
			];
		}
	}
	return $output;
}

/*******************************/
/* getStudentsListsDepartement()
Liste les étudiants d'un département par semestre actif

Entrée :
	$dep : [string] département - exemple : 'MMI'

Sortie :
	[
		{
			'titre': 'Nom du semestre',
			'semestre_id': '132',
			'groupes': ['groupe 1', 'groupe 2', etc.], // Exemple : TP11, TP12, etc.
			'etudiants': [
				{
					'nom': 'nom de l'étudiant',
					'prenom': 'prenom de l'étudiant',
					'groupe': 'groupe 1'
				},
				etc.
			]
		},
		etc. avec les autres semestres d'un département, exemple : 1er année, 2ième année...
	]
	
*******************************/
function getStudentsListsDepartement($dep){
	$Scodoc = new Scodoc();
	$dataSEM = $Scodoc->getDepartmentSemesters($dep);
	$dataSEM = getDepartmentSemesters($dep);
	//var_dump($dataSEM);die();
	$output = [];
	foreach($dataSEM as $value){
		$value = (object) $value;
		$data_students = (object) getStudentsInSemester($dep, $value->semestre_id);
		$output[] = [
			'titre' => $value->titre,
			'semestre_id' => $value->semestre_id,
			'groupes' => $data_students->groupes,
			'etudiants' =>  $data_students->etudiants
		];
	}
	return $output;
}

/*******************************/
/* getStudentDepartment()
Récupère le département d'un étudiant à partir de son numéro d'étudiant

Entrée :
	$nip : [string] numéro d'étudiant - exemple : "21600306"

Sortie :
	"département" - exemple : "MMI"
*/
function getStudentDepartment($nip){
	return Ask_Scodoc(
		'/get_etud_dept',
		'',
		[
			'code_nip' => $nip
		]
	);
}

/*******************************/
/* getStudentsInSemester()
Liste de tous les étudiants dans un semestre

Entrées : 
	$dep : [string] département - exemple : MMI
	$sem : [string] code semestre Scodoc - exemple : 171

Sortie :
	{
		'groupes' => ['groupe 1', 'groupe2', etc.], 
		'etudiants' => [
			{
				'nom' => 'nom de l'étudiant',
				'prenom' => 'prenom de l'étudiant',
				'groupe' => 'groupe de l'étudiant',
				'num_etudiant' => 'numero de l'étudiant',
				'email' => 'email UHA de l'étudiant'
			},
			etc.
		]
	}

*******************************/
function getStudentsInSemester($dep, $sem){
	$json = json_decode(
		Ask_Scodoc(
			'/Scolarite/groups_view',
			$dep,
			[
				'formsemestre_id' => $sem,
				'with_codes' => 1,
				'format' => 'json'
			]
		)
	);

	$groupes = [];
	$output_json = [];
	foreach($json as $value){
		$groupe = findTP($value);
		if(!in_array($groupe, $groupes)){
			$groupes[] = $groupe;
		}

		$output_json[] = [
			'nom' => $value->nom_disp,
			'prenom' => $value->prenom,
			'groupe' => $groupe,
			'num_etudiant' => $value->code_nip,
			'email' => Annuaire::getStudentIdCASFromNumber($value->code_nip)
			// 'num_ine' => $value->code_ine
			// 'email_perso' => $value->emailperso
		];
	}
	sort($groupes);
	return [
		'groupes' => $groupes, 
		'etudiants' => $output_json
	];
}

function findTP($json){
	// Recherche du groupe TP dans la key Pxxxx
	//$output = [];
	foreach($json as $key => $value){
		if(is_numeric($key)){
			return $json->$key;
			//$output[] = $json->$key;
		}
	};
	//return $output;
}

/*******************************/
/* UEAndModules()
Liste les UE et modules d'un département + semestre

Entrées : 
	$dep : [string] département - exemple : MMI
	$sem : [string] code semestre Scodoc - exemple : 871

Sortie :
	[
		{
			UE: "UE1 nom de l'UE",
			modules: [
				{
					"titre": "nom du module 1",
					"code": "W511" // Code scodoc du module
				},
				etc.
			]
		},
		etc.
	]

*******************************/
function UEAndModules($dep, $sem){
	$json = json_decode(Ask_Scodoc(
		'/Scolarite/Notes/formsemestre_description',
		$dep,
		[
			'formsemestre_id' => $sem,
			'format' => 'json'
		]
	));

	array_pop($json); // Supprime le récapitulatif de toutes les UE
	$output_json = [];

	/* 
	Listes des UE et des Modules les uns après les autres 
	Données dispo :
		Code: 'W511',				// null si c'est une UE
		Coef.: '0.5',				// null si c'est une UE
		Inscrits: '12',				// null si c'est une UE
		Module: 'Ecriture numérique',
		Responsable: 'Graef D.',	// null si c'est une UE
		UE: 'UE1 Culture Com &amp; Entreprise',
	*/
	foreach($json as $value){
		if($value->Module != 'Bonus'){
			
			if($value->Responsable == NULL){
				$output_json[] = [
					'UE' => $value->UE,
					'modules' => []
				];
			}else{
				$output_json[count($output_json)-1]['modules'][] = [
					'titre' => $value->Module,
					'code' => $value->Code
				];
					
			}
		}
	}

	return $output_json;
}
