( function( $, inlineEditPost ) {
	if ( ! inlineEditPost || ! window.lithiaServicesQuickEdit ) {
		return;
	}

	var originalEdit = inlineEditPost.edit;
	var rows = window.lithiaServicesQuickEdit.rows || {};

	inlineEditPost.edit = function( id ) {
		originalEdit.apply( this, arguments );

		var postId = 0;

		if ( typeof id === 'object' ) {
			postId = parseInt( this.getId( id ), 10 );
		} else {
			postId = parseInt( id, 10 );
		}

		if ( ! postId || ! rows[ postId ] ) {
			return;
		}

		var editRow = $( '#edit-' + postId );
		var rowData = rows[ postId ];

		if ( ! editRow.length ) {
			return;
		}

		editRow.find( 'input[name="lithia_service_price"]' ).val( rowData.price || '' );
		editRow.find( 'input[name="lithia_service_homepage_spotlight"]' ).prop( 'checked', !! rowData.homepageSpotlightEnabled );
		editRow.find( 'input[name="lithia_service_homepage_spotlight_order"]' ).val( rowData.homepageSpotlightOrder || 0 );
		editRow.find( 'input[name="lithia_service_provider_ids[]"]' ).prop( 'checked', false );

		( rowData.providerIds || [] ).forEach( function( providerId ) {
			editRow.find( 'input[name="lithia_service_provider_ids[]"][value="' + providerId + '"]' ).prop( 'checked', true );
		} );
	};
}( jQuery, window.inlineEditPost ) );
