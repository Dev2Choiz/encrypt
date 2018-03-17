# Encrypt

Encrypt permet de faire differents traitements sur de longue chaines de caracteres :
- Encodage d'un texte d'une base numérique à une base.
- Decodage d'un texte encodé sous une base numérique.
- Base 64


## Installation

Installation avec git

```bash
git clone git@github.com:Dev2Choiz/testformstep.git testformstep
cd testformstep
./launch.sh
```

le script *launch.sh* *build* puis *run* tous les containers necessaires
au bon fonctionnement l'appli.
Il lance ensuite le script *initAppli.sh* qui :
- Supprime les eventuelles dossier *vendor* et fichier *composer.lock*.
- Lance composer install 
- Crée la base de données et ses fixtures
 
L'application devrait maintenant être disponible sur l'url :
[http://localhost:8080/formstep/web/app_dev.php](http://localhost:8080/formstep/web/app_dev.php)
