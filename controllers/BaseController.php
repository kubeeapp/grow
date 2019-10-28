<?php

namespace Grocy\Controllers;

use \Grocy\Services\DatabaseService;
use \Grocy\Services\ApplicationService;
use \Grocy\Services\LocalizationService;
use \Grocy\Services\UsersService;

class BaseController
{
	public function __construct(\Slim\Container $container) {
		$databaseService = new DatabaseService();
		$this->Database = $databaseService->GetDbConnection();
		
		$localizationService = new LocalizationService(GROCY_CULTURE);
		$this->LocalizationService = $localizationService;

		$applicationService = new ApplicationService();
		$versionInfo = $applicationService->GetInstalledVersion();
		$container->view->set('version', $versionInfo->Version);
		$container->view->set('releaseDate', $versionInfo->ReleaseDate);

		$container->view->set('__t', function(string $text, ...$placeholderValues) use($localizationService)
		{
			return $localizationService->__t($text, $placeholderValues);
		});
		$container->view->set('__n', function($number, $singularForm, $pluralForm) use($localizationService)
		{
			return $localizationService->__n($number, $singularForm, $pluralForm);
		});
		$container->view->set('GettextPo', $localizationService->GetPoAsJsonString());

		$container->view->set('U', function($relativePath, $isResource = false) use($container)
		{
			return $container->UrlManager->ConstructUrl($relativePath, $isResource);
		});

		$embedded = false;
		if (isset($container->request->getQueryParams()['embedded']))
		{
			$embedded = true;
		}
		$container->view->set('embedded', $embedded);

		$constants = get_defined_constants();
		foreach ($constants as $constant => $value)
		{
			if (substr($constant, 0, 19) !== 'GROCY_FEATURE_FLAG_')
			{
				unset($constants[$constant]);
			}
		}
		$container->view->set('featureFlags', $constants);

		$container->view->set('userentitiesForSidebar', $this->Database->userentities()->where('show_in_sidebar_menu = 1')->orderBy('name'));

		try
		{
			$usersService = new UsersService();
			if (defined('GROCY_USER_ID'))
			{
				$container->view->set('userSettings', $usersService->GetUserSettings(GROCY_USER_ID));
			}
			else
			{
				$container->view->set('userSettings', null);
			}
		}
		catch (\Exception $ex)
		{
			// Happens when database is not initialised or migrated...
		}

		$this->AppContainer = $container;
	}

	protected $AppContainer;
	protected $Database;
	protected $LocalizationService;
}
