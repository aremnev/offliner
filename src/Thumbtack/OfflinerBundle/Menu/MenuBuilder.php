<?php
namespace Thumbtack\OfflinerBundle\Menu;

    use Knp\Menu\FactoryInterface;
    use Symfony\Component\HttpFoundation\Request;
    /**
     * Main menu builder.
     * For render menu you can use this code:
     * {{ knp_menu_render('main') }}
     */
class MenuBuilder
{
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }
    public function mainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild('About',array('route' => 'about'));
        $menu->addChild('Contact',array('uri' => '#'));
        $menu->setChildrenAttribute('class', 'nav');
        return $menu;
    }

}