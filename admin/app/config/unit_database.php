<?php
$unit_database = TSession::getValue('unit_database');
return TConnection::getDatabaseInfo( $unit_database );
