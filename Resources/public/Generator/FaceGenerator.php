<?php
// Fonction de vérification d'existance de l'image
function is404($filename)
{
    $handle = curl_init($filename);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    if ($httpCode >= 200 && $httpCode < 300)
        return false;
    else
        return true;
}

// Variable d'environnement _GET
$pseudo = trim(@$_GET['pseudo']);
$size = trim(@$_GET['size']);

// Pseudo du joueur à utiliser s'il n'est pas mentionné
if(empty($pseudo))
{
    // Skin Minecraft par defaut
    $pseudo = 'char.png';
}

// Taille de l'image à utiliser si elle n'est pas mentionnée
if(empty($size))
{
    // Taille du skin par défaut à 64 pixels
    $size = '64';
}

// Skin Minecraft demandé
$filename = 'http://s3.amazonaws.com/MinecraftSkins/' . $pseudo . '.png';

// Exécution de la fonction "is404" pour vérifier si le fichier image existe
if(is404($filename) || empty($pseudo))
{
    // Skin Minecraft par défaut
    $filename = 'http://s3.amazonaws.com/MinecraftSkins/char.png';
}

// Génération de l'image
header('Content-type: image/png');

$img_1 = imagecreatetruecolor($size, $size);
$img_2 = imagecreatefrompng($filename);
imagecopyresampled($img_1, $img_2, 0, 0, 8, 8, $size, $size, 8, 8);

imagepng($img_1);
?>