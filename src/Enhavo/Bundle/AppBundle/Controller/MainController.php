<?php
/**
 * Created by PhpStorm.
 * User: gseidel
 * Date: 2019-02-17
 * Time: 22:20
 */

namespace Enhavo\Bundle\AppBundle\Controller;

use Enhavo\Bundle\AppBundle\Menu\MenuManager;
use Enhavo\Bundle\AppBundle\Template\TemplateTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    use TemplateTrait;

    /**
     * @var MenuManager
     */
    private $menuManager;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var array
     */
    private $brandingConfig;

    public function __construct(MenuManager $menuManager, $projectDir, $brandingConfig)
    {
        $this->menuManager = $menuManager;
        $this->projectDir = $projectDir;
        $this->brandingConfig = $brandingConfig;
    }

    public function indexAction(Request $request)
    {
        $state = $this->getState($request);
        $data = [
            'view' => [
                'id' => null,
            ],
            'menu' => [
                'items' => $this->menuManager->createMenuViewData(),
                'open' => true,
            ],
            'view_stack' => [
                'width' => 0,
                'views' => $state['views'],
            ],
            'branding' => [
                'logo' => $this->brandingConfig['logo'],
                'enable' => $this->brandingConfig['enable'],
                'enableVersion' => $this->brandingConfig['enable_version'],
                'enableCreatedBy' => $this->brandingConfig['enable_created_by'],
                'text' => $this->brandingConfig['text'],
                'version' => $this->brandingConfig['version'],
                'backgroundImage' => $this->brandingConfig['background_image']
            ]
        ];

        return $this->render($this->getTemplate('admin/main/index.html.twig'), [
            'data' => $data,
            'routes' => $this->getRoutes(),
            'translations' => $this->getTranslations(),
        ]);
    }

    private function getState(Request $request)
    {
        $default = [
            'views' => [],
        ];

        if(!$request->query->has('state')) {
            return $default;
        }
        $state = $request->query->get('state');
        $state = base64_decode($state);
        $state = gzuncompress($state);
        $state = json_decode($state, true);
        if($state === null) {
            return $default;
        }
        $views = $state['views'];
        foreach($views as &$view) {
            $view['component'] = 'iframe-view';
            $view['loaded'] = false;
        }
        return [
            'views' => $views,
        ];
    }

    private function getRoutes()
    {
        $file = $this->projectDir.'/public/js/fos_js_routes.json';
        if(file_exists($file)) {
            return file_get_contents($file);
        }
        return null;
    }

    private function getTranslations()
    {
        $dumper = $this->get('enhavo_app.translation.translation_dumper');
        $translations = $dumper->getTranslations('javascript', $this->container->get('enhavo_app.locale_resolver')->resolve());
        return $translations;
    }
}
