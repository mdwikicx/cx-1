( function () {
	'use strict';

	/* Override for ve.ui.MWReferenceContextItem.prototype.getRendering as it creates
	 * a ve.ui.MWPreviewElement with default parameters. ve.ui.MWPreviewElement has
	 * useView configuration as false by default and causes not rendering the references
	 * from its DOM model as required for content translation.
	 *
	 * Get a DOM rendering of the reference.
	 *
	 * @private
	 * @return {jQuery} DOM rendering of reference
	**/
	ve.ui.MWReferenceContextItem.prototype.getRendering = function getReferenceRendering() {
		const refNode = this.getReferenceNode();
		if ( refNode ) {
			this.view = new ve.ui.MWPreviewElement( refNode, {
				useView: true
			} );
			// The $element property may be rendered into asynchronously, update the context's size when the
			// rendering is complete if that's the case
			this.view.once( 'render', this.context.updateDimensions.bind( this.context ) );
			return this.view.$element;
		} else {
			return $( '<div>' )
				.addClass( 've-ui-mwReferenceContextItem-muted' )
				.text( ve.msg( 'cite-ve-referenceslist-missingref' ) );
		}
	};

	function addCxSubclass( parentContextItem ) {
		const contextItem = function CXGeneratedMWCitationContextItem() {
			// Parent constructor
			parentContextItem.apply( this, arguments );
		};

		OO.inheritClass( contextItem, parentContextItem );

		contextItem.prototype.getRendering = function () {
			return contextItem.super.prototype.getRendering.apply( this, arguments );
		};

		ve.ui.contextItemFactory.register( contextItem );
	}

	ve.ui.mwCitationTools.forEach( function ( tool ) {
		const contextName = 'cite-' + tool.name;
		const parentContextItem = ve.ui.contextItemFactory.lookup( contextName );
		addCxSubclass( parentContextItem );
	} );

}() );
