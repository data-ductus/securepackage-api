<?php
    function db_connect() {
        $servername = "192.168.99.100:3306";
        $username = "root";
        $password = "tcuser";
        $database = "securepackage";

        return new mysqli($servername, $username, $password, $database);
    }

