<?php

// https://www.pierre-giraud.com/php-mysql-apprendre-coder-cours/introduction-programmation-orientee-objet/

/****************************/
/* Class Auth
	Classe permettant d'instancier le processus d'authentification

	Si la personne n'est pas authentifiée, un JSON de redirection est renvoyé :
		{
			'redirect' => '/services/doAuth.php' // URL vers la page pour s'authentifier
		}

	Méthodes publiques :
		Auth->getSessionName() : renvoie le mail de la personne => string 'jean.dupond@uha.fr'
		Auth->getStatut() : renvoie le statut de la personne => int ETUDIANT | PERSONNEL | ADMINISTRATEUR | INCONNU
		
/****************************/

	interface Authentification{
		public function getSessionName();
		public function getStatut();
	}

	require 'CAS_Authentification.class.php';

	class Auth extends CAS_authentification implements Authentification{	
	/* 
		private function defaultAuth(){
			// Si besoin, votre propre implémentation en surchargeant cette méthode
			// Cette méthode définit l'authentification par défaut
			// Il n'y a pas besoin de toucher au reste (LDAP, authentification par token, etc.)
		}
	*/
	}
?>