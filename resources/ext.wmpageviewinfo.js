( function ( $, mw ) {
	$( function () {
		var $count = $( '.mw-wmpvi-month' ),
			count = $count.text(),
			info = mw.config.get( 'wgWMPageViewInfo' );

		// Turn it into an <a> tag so it's obvious you can click on it
		$count.html( mw.html.element( 'a', { href: '#' }, count ) );

		$count.click( function ( e ) {
			var dialog, windowManager;
			e.preventDefault();
			function MyProcessDialog( config ) {
				MyProcessDialog.parent.call( this, config );
			}
			OO.inheritClass( MyProcessDialog, OO.ui.ProcessDialog );

			MyProcessDialog.static.title = mw.msg( 'wmpvi-range', info.start, info.end );
			MyProcessDialog.static.actions = [
				{ label: mw.msg( 'wmpvi-close' ), flags: 'safe' }
			];

			MyProcessDialog.prototype.initialize = function () {
				MyProcessDialog.parent.prototype.initialize.apply( this, arguments );
				this.content = new OO.ui.PanelLayout( { padded: true, expanded: false } );
				this.$body.append( this.content.$element );
				mw.drawVegaGraph( this.content.$element[ 0 ], info.graph );
			};
			MyProcessDialog.prototype.getActionProcess = function ( action ) {
				var dialog = this;
				if ( action ) {
					return new OO.ui.Process( function () {
						dialog.close( { action: action } );
					} );
				}
				return MyProcessDialog.parent.prototype.getActionProcess.call( this, action );
			};

			windowManager = new OO.ui.WindowManager();
			$( 'body' ).append( windowManager.$element );

			dialog = new MyProcessDialog( { size: 'large' } );
			windowManager.addWindows( [ dialog ] );
			windowManager.openWindow( dialog );
		} );

	} );
}( jQuery, mediaWiki ) );
