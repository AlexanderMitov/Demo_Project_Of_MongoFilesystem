<?php
require_once 'vendor/autoload.php';//including the Composer autoloader
use \MongoFilesystem\MongoFilesystem;
use \MongoFilesystem\MongoFolder; //representing a folder on the MongoFilesystem
use \MongoFilesystem\MongoFile; //representing a file on the MongoFilesystem
use \MongoFilesystem\Folder; //representing a folder on the local filesystem
use \MongoFilesystem\File; //representing a file on the local filesystem
use \MongoFilesystem\Renderer\JSONFolderRenderer; //A JSON renderer for a MongoFolder
use \MongoClient; //The MongoDB PHP API for connection

$connection = new MongoClient('mongodb://127.0.0.1:27017'); //connection to localhost
$db = $connection->selectDB('local'); //selecting the DB to be used by MongoFilesystem
$fs = new MongoFilesystem($db); //creating an instance by passing the DB object

//everything is ready to be used
$relativePath = 'files/'; //the relative path of the folder we want to upload to the index.php
$pathToFilesFolder = __DIR__ . DIRECTORY_SEPARATOR . $relativePath;
$localFolder = new Folder($pathToFilesFolder); //creating an instance of \MongoFilesystem\Folder by passing the absolute path to the folder on the local filesystem
/*NOTE: if a folder with a path already exists but we upload a folder with the same path anyway,
* the uploaded folder will have a name with an appended counter on it
* e.g folderName(1), folderName(2) and etc.
*/
if($fs->folderWithPathExists($relativePath))
{
	//a folder with the path already exists in the MongoFilesystem
	//so we're going to use the updateFolder functionality
	$existingFolderID = $fs->getFolderIDByPath($relativePath); //finding the MongoId of the existing folder
	$fs->updateFolder($existingFolderID, $localFolder);
}
else
{
	//A folder with this relative path does not exist, so we can just upload our folder
	$fs->uploadFolder($localFolder);
}
$mongoFolder = $fs->getFolderByPath($relativePath); //getting a MongoFolder instance of the folder with the passed path
$renderer = new JSONFolderRenderer($mongoFolder); //creating an instance of the JSONFolderRenderer by passing the MongoFolder object
/*
* NOTE: You can't use output the rendered folder and download the folder as a zip at the same time
*/
echo $renderer->render(); //the render() method renders the passed folder in the constructor and returns the rendered view as a string
//$fs->downloadAndOutputFolder($mongoFolder); //Creates a zip of the folder and passes it to the browser client
$connection->close();//now close the connection to the MongoDB server
echo "WHAAAT";
?>