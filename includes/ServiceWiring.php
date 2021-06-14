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

		$endpoint = $extensionConfig->get( 'PageViewInfoWikimediaEndpoint' );
		$project = $extensionConfig->get( 'PageViewInfoWikimediaDomain' )
			?: $mainConfig->get( 'ServerName' );

		$service = new WikimediaPageViewService( $endpoint, [ 'project' => $project ],
			$extensionConfig->get( 'PageViewInfoWikimediaRequestLimit' ) );
		$service->setLogger( $logger );
		$service->setOriginalRequest( RequestContext::getMain()->getRequest() );

		// Give extensions a chance to use other PageViewService than WikimediaPageViewService
		Hooks::run( 'PageViewInfoAfterPageViewService', [ &$service ] );

		$cachedService = new CachedPageViewService( $service, $cache );
		$cachedService->setCachedDays( $cachedDays );
		$cachedService->setLogger( $logger );
		return $cachedService;
	},
];
