
## Creation d'un mini reseau social avec symfony 
symfony new synfoconnect --version="7.*" --webapp
configuration de .env.local 
synchroniser  compose.yaml 
lancer le docker compose 

puis creer 

commande php 

**cree un projet php**
symfony new synfoconnect --version="7.*" --webapp

**creer une base donne**
symfony console doctrine:database:create

**Lancer le server symfony**
 symfony serve 



**Abstract Controler**
est une classe qui implemente des fonctions permettant de gerer les utilisateur 
le plus interressant 


**creation d une entite**

symfony console make:user

**Effectuer une migrration**
symfony console make:migration

***Apppliquer la migration**
symfony console doctrine:migrations:migrate


Comment peuple sa BD 

**installation fixture**
symfony composer req orm-fixtures --dev

apres avoir ecrit les fixture dans le fichier src/DataFixtures/

puis lancer  symfony console doctrine:fixtures:load