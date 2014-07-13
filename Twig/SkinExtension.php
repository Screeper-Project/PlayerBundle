<?php
namespace MinecraftProject\PlayerBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SkinExtension extends \Twig_Extension
{
    protected $container;
    protected $web_path;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->web_path = $this->container->get('kernel')->getRootDir() . '/../web';
    }

    public function faceURL($pseudo)
    {
        $path = $this->getWebPath();

        return $path . 'bundles/player/Generator/FaceGenerator.php?pseudo=' . $pseudo;
    }

    public function getFunctions() {
        return array(
            'generate_face_url' => new \Twig_Function_Method($this, 'faceURL'),
        );
    }

    public function getName()
    {
        return 'mp_skin_extension';
    }
}