
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

**Modification d une entite existante**
symfony console make:entity Name_entity
**Effectuer une migrration**
symfony console make:migration

***Apppliquer la migration**
symfony console doctrine:migrations:migrate

**Creation d'un controller et sa vue**
 symfony console make:controller Home

Comment peuple sa BD 

**installation fixture**
symfony composer req orm-fixtures --dev

apres avoir ecrit les fixture dans le fichier src/DataFixtures/

puis lancer  symfony console doctrine:fixtures:load


# Conception du Projet symfoConnect 

**Etape**

configuration du fichier  compose.yaml, definition de l'architecture de notre projet 
avec 4 different different services 
- Database MYSQL 
- phpmyadmin qui nous donne un cadre visuel mon la gestion de notre base de donnee 
- mailler 