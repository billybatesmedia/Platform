( function( blocks, blockEditor, components, element, i18n, serverSideRender ) {
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var createElement = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var ServerSideRender = serverSideRender;

	function renderPreview( blockName, attributes, className ) {
		return createElement(
			'div',
			{ className: className },
			createElement( ServerSideRender, {
				block: blockName,
				attributes: attributes
			} )
		);
	}

	function renderTonePanel( attributes, setAttributes ) {
		return createElement(
			PanelBody,
			{
				title: __( 'Style', 'lithia-web-service-theme' ),
				initialOpen: false
			},
			createElement( SelectControl, {
				label: __( 'Color Mode', 'lithia-web-service-theme' ),
				value: attributes.tone || 'light',
				options: [
					{ label: __( 'Light', 'lithia-web-service-theme' ), value: 'light' },
					{ label: __( 'Dark', 'lithia-web-service-theme' ), value: 'dark' }
				],
				onChange: function( value ) {
					setAttributes( { tone: value } );
				}
			} )
		);
	}

	registerBlockType( 'lithia/service-page', {
		apiVersion: 2,
		title: __( 'Service Page', 'lithia-web-service-theme' ),
		description: __( 'Field-driven service template that pulls data from the Service Page Fields meta box.', 'lithia-web-service-theme' ),
		icon: 'layout',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'full' }
		},
		supports: {
			align: [ 'full', 'wide' ],
			html: false
		},
		edit: function( props ) {
			return createElement(
				Fragment,
				null,
				renderPreview( 'lithia/service-page', props.attributes, 'lithia-service-page-preview' )
			);
		},
		save: function() {
			return null;
		}
	} );

	registerBlockType( 'lithia/service-spotlight-loop', {
		apiVersion: 2,
		title: __( 'Service Spotlight Loop', 'lithia-web-service-theme' ),
		description: __( 'Homepage slider that rotates spotlighted services using their excerpts and CTA fields.', 'lithia-web-service-theme' ),
		icon: 'slides',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'wide' },
			tone: { type: 'string', default: 'light' },
			eyebrow: { type: 'string', default: 'Service Spotlight' },
			heading: { type: 'string', default: 'Start with one focused service' },
			intro: { type: 'string', default: 'Rotate selected services here using each service excerpt as the slide copy.' },
			showArchiveCta: { type: 'boolean', default: true },
			archiveLabel: { type: 'string', default: 'View All Services' },
			archiveUrl: { type: 'string', default: '/services/' }
		},
		supports: {
			align: [ 'full', 'wide' ],
			anchor: true,
			html: false
		},
		edit: function( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return createElement(
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					renderTonePanel( attributes, setAttributes ),
					createElement(
						PanelBody,
						{
							title: __( 'Section Content', 'lithia-web-service-theme' ),
							initialOpen: true
						},
						createElement( TextControl, {
							label: __( 'Eyebrow', 'lithia-web-service-theme' ),
							value: attributes.eyebrow,
							onChange: function( value ) {
								setAttributes( { eyebrow: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Heading', 'lithia-web-service-theme' ),
							value: attributes.heading,
							onChange: function( value ) {
								setAttributes( { heading: value } );
							}
						} ),
						createElement( TextareaControl, {
							label: __( 'Intro', 'lithia-web-service-theme' ),
							value: attributes.intro,
							onChange: function( value ) {
								setAttributes( { intro: value } );
							}
						} )
					),
					createElement(
						PanelBody,
						{
							title: __( 'Archive CTA', 'lithia-web-service-theme' ),
							initialOpen: false
						},
						createElement( ToggleControl, {
							label: __( 'Show Archive CTA', 'lithia-web-service-theme' ),
							checked: !! attributes.showArchiveCta,
							onChange: function( value ) {
								setAttributes( { showArchiveCta: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Archive Label', 'lithia-web-service-theme' ),
							value: attributes.archiveLabel,
							onChange: function( value ) {
								setAttributes( { archiveLabel: value } );
							}
						} ),
						createElement( TextControl, {
							label: __( 'Archive URL', 'lithia-web-service-theme' ),
							value: attributes.archiveUrl,
							onChange: function( value ) {
								setAttributes( { archiveUrl: value } );
							}
						} )
					)
				),
				renderPreview( 'lithia/service-spotlight-loop', attributes, 'lithia-service-spotlight-loop-preview' )
			);
		},
		save: function() {
			return null;
		}
	} );

	registerBlockType( 'lithia/provider-page', {
		apiVersion: 2,
		title: __( 'Provider Page', 'lithia-web-service-theme' ),
		description: __( 'Provider template that pulls the bio, featured image, and related services from the Provider post.', 'lithia-web-service-theme' ),
		icon: 'id-alt',
		category: 'lithai-blocks',
		attributes: {
			align: { type: 'string', default: 'full' }
		},
		supports: {
			align: [ 'full', 'wide' ],
			html: false
		},
		edit: function( props ) {
			return createElement(
				Fragment,
				null,
				renderPreview( 'lithia/provider-page', props.attributes, 'lithia-provider-page-preview' )
			);
		},
		save: function() {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n, window.wp.serverSideRender );
