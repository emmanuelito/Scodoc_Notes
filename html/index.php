<?php 
	$path = realpath($_SERVER['DOCUMENT_ROOT'] . '/..');
	include_once "$path/includes/default_config.php";
?>
<!DOCTYPE html>
<html lang=fr>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width">
		<title>Relevé de notes</title>
		<link rel="manifest" href="manifest.json">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
		<meta name="theme-color" content="#0084b0">
		<link rel="apple-touch-icon" href="images/icons/192x192.png">
		<style>
			<?php include $_SERVER['DOCUMENT_ROOT']."/assets/header.css"?>
/**********************/
/* Gestion de semestres */
/**********************/
			.studentPic{
				float: left;
				border-radius: 8px;
				width: 52px;
				height: auto;
    			margin-right: 16px;
			}
			.semestres{
				display: flex;
				flex-wrap: wrap;
			}
			.semestres>label{
				cursor: pointer;
			}
			.semestres input{
				display: none;
			}
			.semestres>label>div{
				background: #FFF;
				padding: 8px 16px;
				margin: 8px;
				font-size: 18px;
				text-align: right;
				border-radius: 8px;
				box-shadow: 0 2px 2px rgb(0 0 0 / 26%);
			}
			.semestres>label>div:hover{
				outline: 2px solid #424242;
			}
			.semestres>label>div>div:nth-child(2){
				font-weight: bold;
				color: #09c;
			}
			.semestres input:checked+div{
				background: #0C9;
				color: #FFF;
			}
			.semestres input:checked+div>div:nth-child(2){
				color: #FFF;
			}

			form{
				text-align: right;
			}
			form>button{
				border: none;
				background: #09c;
				padding: 8px 32px;
				color: #FFF;
				border-radius: 8px;
				cursor: pointer;
			}
/**********************/
/* Zone absences */
/**********************/
			h2{
				background: #09C;
			}
			.absences>div{
				display: grid;
				grid-template-columns: repeat(5, auto);
				gap: 2px;
				padding: 4px;
				overflow: auto;
			}
			.absences>div>div{
				background: #FFF;
				box-shadow: 0 2px 2px #888;
				padding: 4px 8px;
				border-radius: 4px;
			}
			.absences>div>.entete{
				background:#0c9;
				color: #FFF;
			}
			.absences>div>.enseignant{
				text-transform: capitalize;
			}
			.absences>div>.absent{background: #ec7068; color: #FFF;}
			.absences>div>.retard{background: #f3a027; color: #FFF;}
			.absences>div>.justifie{background: #0c9 !important}

			.absences>.toutesAbsences>.absent:before{content:"Absent"}
			.absences>.toutesAbsences>.retard:before{content:"Retard"}
			.absences>.toutesAbsences>.justifie:before{content:"Justifiée"}

			.absences>.totauxAbsences{
				grid-template-columns: repeat(3, auto);
				margin-top: 16px;
			}
			.totauxAbsences>div:nth-child(1){
				background: #09c;
			}

			.hideAbsences .absences{
				display: none;
			}

/**********************/
/* Mode personnels    */
/**********************/
			.etudiantHide{
				display: none;
			}
			.personnel .eval{
				cursor: initial;
			}
			.personnel .etudiantHide{
				display: block;
				margin: 20px auto 20px auto;
			}
			.etudiantHide>input{
				border: 1px solid #ef5350;
				padding: 20px;
				border-radius: 20px;
				font-size: 18px;
				display: inline-block;
				margin: 10px;
			}
		</style>
		<meta name=description content="Relevé de notes de l'<?php echo $Config->nom_IUT; ?>">
	</head>
	<body class="<?php
		if($Config->afficher_absences == false){
			echo 'hideAbsences';
		}
	?>">
		<?php 
			$h1 = 'Relevé de notes';
			include $_SERVER['DOCUMENT_ROOT']."/assets/header.php";
		?>
		<main>
			<a href="avatar.php" aria-label="Changer la photo">
				<img alt="Photo de profil" class=studentPic src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" width=350 height=450>
			</a>
			<p>
				Bonjour <span class=nom></span>.
			</p>
			<p>
				<i>
					Ce relevé de notes est provisoire, il est fourni à titre informatif et n'a aucune valeur officielle.<br>
					La moyenne affichée correspond à la moyenne coefficientée des modules qui ont des notes.
				</i>
			</p>
			<div class=etudiantHide>
				Vous êtes un personnel de l'IUT , <input required list=etudiants name=etudiant placeholder="Choisissez un étudiant" onchange="loadSemesters(this);this.blur()">
				<datalist id=etudiants></datalist>
			</div>
			<div class=semestres></div>
			<hr>
			<div class=wait></div>
			<div class=releve></div>
			<hr>

			<div class="absences">
				<h2>Rapport d'absences</h2>
				<p><i>
				Les causes de l’absence doivent être notifiées par écrit à l'aide d'un justificatif dans les 48 heures à compter du début de l’absence au secrétariat du département. Voir règlement intérieur pour les motifs légitimes d'absence.
				</i></p>
				<div class=toutesAbsences></div>
				<h3>Totaux</h3>
				<i>Chaque département peut décider d'un malus en fonction des absences injustifiées.
				</i>
				<div class=totauxAbsences></div>
			</div>

			<hr>
			<small>Ce site utilise deux cookies permettant l'authentification au service et une analyse statistique anonymisée des connexions ne nécessitant pas de consentement selon les règles du RGPD.</small><br>
			<small>Application réalisée par Sébastien Lehmann, enseignant MMI à l'IUT de Mulhouse - <a href="maj.php?-no-sw">version <?php echo $Config->passerelle_version; ?></a> - <a href="https://github.com/SebL68/Scodoc_Notes">code source</a></small>
		</main>

		<div class=auth>
			Authentification en cours ...
		</div>

		<script src="assets/js/releve-dut.js"></script>
		<script src="assets/js/releve-but.js"></script>
		<script>
/**************************/
/* Service Worker pour le message "Installer l'application" et pour le fonctionnement hors ligne PWA
/**************************/		
			if('serviceWorker' in navigator){
				navigator.serviceWorker.register('sw.js');
			}
/**************************/
/* Début
/**************************/
			let nip = "";
			let statut = "";
			checkStatut();
			document.querySelector("#notes")?.classList.add("navActif");
			<?php
				include "$path/includes/clientIO.php";
			?>
/*********************************************/
/* Vérifie l'identité de la personne et son statut
/*********************************************/			
			async function checkStatut(){
				let data = await fetchData("dataPremièreConnexion");

				nip = data.auth.session;
				statut = data.auth.statut;

				document.querySelector(".studentPic").src = "services/data.php?q=getStudentPic";
				document.querySelector(".nom").innerText = data.auth.name;
				let auth = document.querySelector(".auth");
				auth.style.opacity = "0";
				auth.style.pointerEvents = "none";

				if(data.auth.statut >= PERSONNEL){
					document.querySelector("body").classList.add('personnel');
					if(data.auth.statut >= ADMINISTRATEUR){
						document.querySelector("#admin").style.display = "inherit";
					}
					loadStudents(data.etudiants);
					let etudiant = (window.location.search.match(/ask_student=([a-zA-Z0-9._@-]+)/)?.[1] || "");
					if(etudiant){
						let input = document.querySelector("input");
						input.value = etudiant;
						loadSemesters(input);
					}
				} else {
					document.querySelector("body").classList.add('etudiant');
					feedSemesters(data.semestres);
					showReportCards(data, data.semestres[0], data.auth.session);
					feedAbsences(data.absences);
				}
			}
/*********************************************/
/* Fonction pour les personnels 
	Charge la liste d'étudiants pour en choisir un
/*********************************************/
			async function loadStudents(data){
				let output = "";
				data.forEach(function(e){
					output += `<option value='${e[0]}'>${e[1]}</option>`;
				});
				
				document.querySelector("#etudiants").innerHTML = output;
			}
			
/*********************************************/
/* Charge les semestres d'un étudiant
	Paramètre étudiant pour un personnel qui en choisit un
/*********************************************/
			async function loadSemesters(input = ""){
				if(input){
					nip = input.value;
				}				
				let data = await fetchData("semestresEtudiant" + (input ? "&etudiant=" + nip : ""));
				feedSemesters(data, nip);
				document.querySelector(".semestres>label:nth-child(1)>div").click();
			}
			
			function feedSemesters(data, nip){
				let output = document.querySelector(".semestres");
				output.innerHTML = "";
				for(let i=data.length-1 ; i>=0 ; i--){
					let label = document.createElement("label");
					
					let input = document.createElement("input");
					input.type = "radio";
					input.name = "semestre";
					if(i==data.length-1){
						input.checked = true;
					}

					let vignette = document.createElement("div");
					vignette.innerHTML = `
						<div>${data[i].titre} - ${data[i].annee_scolaire}</div>
						<div>Semestre ${data[i].semestre_id}</div>
					`;
					vignette.dataset.semestre = data[i].formsemestre_id;
					vignette.addEventListener("click", getReportCards);

					label.appendChild(input);
					label.appendChild(vignette);
					output.appendChild(label);
				}

				if(statut >= 20){
					let url = window.location.origin + "/?ask_student=" + nip;
					let div = document.createElement("div");
					div.innerHTML = `<div style="width:100%; margin: 8px;">Lien pour accéder directement aux relevés : <a href=${url}>${url}</a></div>`;
					output.appendChild(div);
				}
			}

/*********************************************/
/* Récupère et affiche le relevé de notes
/*********************************************/
			async function getReportCards(){
				let semestre = this.dataset.semestre;
				let data = await fetchData("relevéEtudiant&semestre=" + semestre + ((nip && statut >= PERSONNEL) ? ("&etudiant=" + nip) : ""));

				showReportCards(data, semestre);
				feedAbsences(data.absences);
			}	

			function showReportCards(data, semestre){
				if(data.relevé.publie == false){
					document.querySelector(".releve").innerHTML = "<h2 style='background: #90c;'>" + data.relevé.message + "</h2>";
				}else if(data.relevé.type == "BUT"){
					document.querySelector(".releve").innerHTML = `
						<?php if($Config->releve_PDF == true){ ?>
							<form action="services/bulletin_PDF.php?sem_id=${semestre}&etudiant=${nip}" target="_blank" method="post">
								<button type="submit">Télécharger le relevé au format PDF</button>
							</form>
						<?php } ?>
						<releve-but></releve-but>`;

					let releve = document.querySelector("releve-but");
					releve.config = {
						showURL: false
					}
					releve.showData = data.relevé;
					releve.shadowRoot.children[0].classList.add("hide_abs");

					/* Styles différent de Scodoc */
					let styles = document.createElement('link');
					styles.setAttribute('rel', 'stylesheet');
					styles.setAttribute('href', 'assets/styles/releve-but-custom.css');
					releve.shadowRoot.appendChild(styles);
					<?php if(file_exists("$path/config/releve-but-local.css") == true){ ?>
					/* Styles locaux */
					styles = document.createElement('style');
					styles.innerText = `<?php include("$path/config/releve-but-local.css"); ?>`;
					releve.shadowRoot.appendChild(styles);
					<?php } ?>
					
					if(!document.body.classList.contains("personnel")){
						document.querySelector(".nom").innerText = data.relevé.etudiant.prenom.toLowerCase();
						releve.shadowRoot.querySelector(".studentPic").src = "services/data.php?q=getStudentPic";
					} else {
						releve.shadowRoot.querySelector(".studentPic").src = "services/data.php?q=getStudentPic&nip=" + nip;
					}
				} else {
					document.querySelector(".releve").innerHTML = "<releve-dut></releve-dut>";
					document.querySelector("releve-dut").showData = [data.relevé, semestre, nip];
					<?php if($Config->releve_PDF == false){ ?>
						document.querySelector("releve-dut").hidePDF = false;
					<?php } ?>
				}
			}

/*********************************************/
/* Affichage des absences
/*********************************************/
			function feedAbsences(data){
				var totaux = {
					justifie: 0,
					absent: 0,
					retard: 0
				};
				let output = "";

				if(Object.entries(data).length){
					Object.entries(data).forEach(([date, listeAbsences])=>{
						listeAbsences.forEach(absence=>{
							if(absence.statut == "present"){
								return;
							}
							if(absence.justifie == true || absence.justifie == "true"){
								totaux.justifie += absence.fin - absence.debut;
							}else{
								if(absence.statut == "retard") {
									totaux[absence.statut] += 1;
								} else {
									totaux[absence.statut] += absence.fin - absence.debut;
								}
								
							}
							output = `
								<div>${date.split("-").reverse().join("/")}</div> 
								<div>${floatToHour(absence.debut)} - ${floatToHour(absence.fin)}</div>
								<div>${absence.matiereComplet}</div>
								<div class=enseignant>${absence.enseignant.split('@')[0].split(".").join(" ")}</div>
								<div class="${(absence.justifie === true || absence.justifie === "true" ) ? "justifie" : absence.statut}"></div>
							` + output;
						})
					})
				} else {
					output = `
						<div>/</div> 
						<div>/</div>
						<div>/</div>
						<div>/</div>
						<div>/</div>
					`
				}
				
				document.querySelector(".absences>.toutesAbsences").innerHTML = `
					<div class=entete>Date</div> 
					<div class=entete>Heures</div>
					<div class=entete>Matière</div>
					<div class=entete>Enseignant</div>
					<div class=entete>Statut</div>
				` + output;

				/* Totaux */

				document.querySelector(".absences>.totauxAbsences").innerHTML = `
					<div class="entete justifie">Nombre justifiées</div>
					<div class="entete absent">Nombre injustifiées</div>
					<div class="entete retard">Nombre retards</div>

					<div>${floatToHour(totaux.justifie)}</div>
					<div>${floatToHour(totaux.absent)}</div>
					<div>${totaux.retard}</div>
				`;
			}

			function floatToHour(heure){
				return Math.floor(heure) + "h"+ ((heure%1*60 < 10)?"0"+heure%1*60 : heure%1*60)
			}
		</script>
	
		<?php 
			include "$path/config/analytics.php";
		?>

<!-- ----------------------------------------------------------------- -->
<!--               Fait avec beaucoup d'amour par                      -->
<!--	   Sébastien Lehmann et Denis Graef - enseignant MMI           -->
<!--																   -->
<!--         Merci à Alexandre Kieffer et Bruno Colicchio.             -->
<!-- ----------------------------------------------------------------- -->
	</body>
</html>
