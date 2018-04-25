# Securepackage-API

API of the centralized version of Secure Package system, which is located [here](https://github.com/data-ductus/securepackage-centralized).

## Dependencies

* MySQLi database.
* PHP MySQL server ([xampp](https://www.apachefriends.org/download.html) is perfect for local deployment).

## Deployment and connection to frontend

* Download the project to a PHP server. 
* Change target path in the `ApiService` of the centralized implementation.
* Create a database instance and run SQL scripts from `database_generation` folder on the database.
* Change target, username and password, accordingly in `helpers/db_connect.php`.