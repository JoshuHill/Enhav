<?php
/**
 * PreviewViewer.php
 *
 * @since 18/11/15
 * @author gseidel
 */

namespace Enhavo\Bundle\AppBundle\Viewer\Viewer;

use Enhavo\Bundle\AppBundle\Viewer\AbstractViewer;
use Enhavo\Bundle\AppBundle\Viewer\OptionAccessor;
use FOS\RestBundle\View\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreviewViewer extends AbstractViewer
{
    protected function getResponse()
    {
        $strategyName = $this->optionAccessor->get('strategy');
        $strategy = $this->container->get('enhavo_app.preview.strategy_resolver')->getStrategy($strategyName);
        $response = $strategy->getPreviewResponse($this->resource, $this->optionAccessor->toArray());
        return $response;
    }

    public function createView($options = []): View
    {
        $view = View::create();
        $view->setResponse($this->getResponse());
        return $view;
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'strategy' => 'service',
            'service' => 'enhavo_app.preview.default_renderer:renderTest'
        ]);
    }

    public function getType()
    {
        return 'preview';
    }
}
