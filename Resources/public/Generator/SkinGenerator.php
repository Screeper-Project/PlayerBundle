<?php
DisplayMinecraftSkin(@$_GET['pseudo']);

function flip(&$img)
{
    $size_x = imagesx($img);
    $size_y = imagesy($img);
    $temp = imagecreatetruecolor($size_x, $size_y);
    $x = imagecopyresampled($temp, $img, 0, 0, ($size_x-1), 0, $size_x, $size_y, 0-$size_x, $size_y);
    return $temp;
}

function DisplayMinecraftSkin($playername = 'char')
{
    $filename = 'http://www.minecraft.net/skin/' . $playername . '.png';
    header('Content-Type: image/png');

    $rendered = imagecreatetruecolor(240, 480);
    $source = imagecreatefrompng($filename);
    $b = 120;
    $s = 8;

    $pink = imagecolorallocate($rendered, 255, 0, 255);
    imagefilledrectangle($rendered, 0, 0, 240, 480, $pink);
    imagecolortransparent($rendered, $pink);
    $fsource = flip($source);

    // Traitement de la tête
    imagecopyresampled($rendered, $source, $b / 2, 0, $s, $s, $b, $b, $s, $s);

    // Traitement des accessoires de la tête
    imagecopyresampled($rendered, $source, $b / 2, 0, $s * 5, $s, $b, $b, $s, $s);

    // Traitement du corps
    imagecopyresampled($rendered, $source, $b / 2, $b, $s * 2.5, $s * 2.5, $b, $b * 1.5, $s, $s * 1.5);

    // Traitement des bras gauche
    imagecopyresampled($rendered, $source, $b * 1.5, $b, $s * 5.5, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);

    // Traitement des bras droit
    imagecopyresampled($rendered, $fsource, 0, $b, $s * 2, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);

    // Traitement des jambes gauche
    imagecopyresampled($rendered, $source, 60, $b * 2.5, $s / 2, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);

    // Traitement des jambes gauche
    imagecopyresampled($rendered, $fsource, $b * 1, $b * 2.5, $s * 7, $s * 2.5, $b / 2, $b * 1.5, $s / 2, $s * 1.5);

    // Sortie de l'image
    imagepng($rendered);

}
?>