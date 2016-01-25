coreb
=====

A Symfony project created on December 2, 2015, 6:52 pm.

http://api.transilien.com/gare/87271395/depart

php app/console server:run


MySQL commands :
change password : mysqladmin -u root password newpassword
connect to database : mysql -u root -pMYPAssWORD
change database : USE databaseName;



Create entity : php app/console doctrine:generate:entity
Update entity : php app/console doctrine:generate:entities BackendAdminBundle:_My_Entity_

Update Database : php bin/console doctrine:schema:update --force
Generate form : php app/console generate:doctrine:form AcmeBlogBundle:Post
Clear cache (dev) : php app/console ca:cl -e=dev
Clear cache (prod) : php app/console ca:cl -e=prod
Debug routes : php bin/console debug:router

Publish assets from public folder : php app/console assets:install


http://www.symfony2cheatsheet.com/
http://stackoverflow.com/questions/9261296/how-to-debug-template-binding-errors-for-knockoutjs