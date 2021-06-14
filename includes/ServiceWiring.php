<?php

namespace MediaWiki\Extensions\PageViewInfo;

use Hooks;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use ObjectCache;
use RequestContext;

return [
	'PageViewService' => function ( MediaWikiServices $services ) {
		$mainConfig = $services->getMainConfig();
		$extensionConfig = $services->getConfigFactory()->makeConfig( 'PageViewInfo' );
		$cache = ObjectCache::getLocalClusterInstance();
		$logger = LoggerFactory::getInstance( 'PageViewInfo' );
		$cachedDays = max( 30, $extensionConfig->get( 'PageViewApiMaxDays' ) );

		if ( Hooks::run( 'PageViewInfoGetPageViewService', [ &$service ] ) ) {
			$endpoint = $extensionConfig->get( 'PageViewInfoWikimediaEndpoint' );
			$project = $extensionConfig->get( 'PageViewInfoWikimediaDomain' )
				?: $mainConfig->get( 'ServerName' );

			$service = new WikimediaPageViewService( $endpoint, [ 'project' => $project ],
				$extensionConfig->get( 'PageViewInfoWikimediaRequestLimit' ) );
			$service->setLogger( $logger );
			$service->setOriginalRequest( RequestContext::getMain()->getRequest() );
		}
		$cachedService = new CachedPageViewService( $service, $cache );
		$cachedService->setCachedDays( $cachedDays );
		$cachedService->setLogger( $logger );
		return $cachedService;
	},
];
