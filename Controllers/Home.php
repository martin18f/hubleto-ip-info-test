<?php

namespace Hubleto\App\Custom\IpInfoTest\Controllers;

class Home extends \Hubleto\Erp\Controller
{

  public function getBreadcrumbs(): array
  {
    return array_merge(parent::getBreadcrumbs(), [
      [ 'url' => 'ipinfotest', 'content' => 'IpInfoTest' ],
    ]);
  }

  public function prepareView(): void
  {
    parent::prepareView();
    $this->viewParams['now'] = date('Y-m-d H:i:s');
    $this->setView('@Hubleto:App:Custom:IpInfoTest/Home.twig');
  }

}