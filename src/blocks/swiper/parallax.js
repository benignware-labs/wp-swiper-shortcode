import { assign } from 'lodash';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { createHigherOrderComponent, compose } = wp.compose;
const { addFilter } = wp.hooks;
const { select } = wp.data;

const {
	withFilters,
	IconButton,
	PanelBody,
	PanelRow,
	TextControl,
	RangeControl,
	SelectControl,
	ToggleControl,
	Toolbar,
	withNotices,
} = wp.components;

const {
	InspectorControls,
	InnerBlocks,
	BlockControls,
	BlockVerticalAlignmentToolbar,
} = wp.editor;

const withInspectorControls = createHigherOrderComponent( function( BlockEdit ) {
    return function( props ) {
			const { getBlockHierarchyRootClientId, getBlock } = select('core/editor');

			if ( ! enableParallaxOnBlocks.includes( props.name ) ) {
	      return <BlockEdit {...props}/>;
	    }

			const rootClientId = getBlockHierarchyRootClientId(props.clientId);
			const rootBlock = getBlock(rootClientId);

			if (rootBlock.name !== 'swiper/swiper' || !rootBlock.attributes.parallax) {
				return <BlockEdit {...props}/>;
			}

			const { attributes } = props;

      return (
        <Fragment>
          <BlockEdit {...props}/>
          <InspectorControls>
            <PanelBody title="Swiper Parallax Settings" initialOpen>
						<ToggleControl
							label="Enable Parallax"
							onChange={(value) => props.setAttributes({
								...attributes,
								parallax: value ? (attributes.parallax || {}) : null
							})}
							checked={!!attributes.parallax}
						/>
						{attributes.parallax && (
							<Fragment>
								<TextControl
									label={ __( 'Parallax x' ) }
									value={attributes.parallax.x}
									onChange={(value) => props.setAttributes({
										...attributes,
										parallax: {
											...(attributes.parallax || {}),
											x: value
										}
									})}
									help={() => `Move element depending on progress by pixel or percentage in horizontal direction`}
								/>
								<TextControl
									label={ __( 'Parallax y' ) }
									value={attributes.parallax.y}
									onChange={(value) => props.setAttributes({
										...attributes,
										parallax: {
											...(attributes.parallax || {}),
											y: value
										}
									})}
									help={() => `Move element depending on progress by pixel or percentage in vertical direction`}
								/>
								<RangeControl
									label={ __( 'Parallax duration' ) }
									value={ attributes.parallax.duration || 600 }
									onChange={(value) => props.setAttributes({
										...attributes,
										parallax: {
											...(attributes.parallax || {}),
											duration: value
										}
									})}
									min={ 200 }
									max={ 2000 }
								/>
							</Fragment>
						)}
            </PanelBody>
          </InspectorControls>
        </Fragment>
      );
    };
}, 'withInspectorControls' );

addFilter( 'editor.BlockEdit', 'swiper/swiper', withInspectorControls );



// Enable parallax on the following blocks
const enableParallaxOnBlocks = [
  'core/paragraph',
];

const addParallaxAttributes = ( settings, name ) => {
    // Do nothing if it's another block than our defined ones.
    if ( ! enableParallaxOnBlocks.includes( name ) ) {
      return settings;
    }

    // Use Lodash's assign to gracefully handle if attributes are undefined
    settings.attributes = assign( settings.attributes, {
      parallax: {
        type: 'object',
        default: null
      },
    });

    return settings;
};

addFilter( 'blocks.registerBlockType', 'swiper/swiper-parallax', addParallaxAttributes );


const addParallaxExtraProps = ( saveElementProps, blockType, attributes ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! enableParallaxOnBlocks.includes( blockType.name ) ) {
    return saveElementProps;
  }

  assign( saveElementProps, {
		'data-swiper-parallax': attributes.parallax && (!!attributes.parallax.value) || undefined,
		'data-swiper-parallax-x': attributes.parallax && attributes.parallax.x || undefined,
		'data-swiper-parallax-y': attributes.parallax && attributes.parallax.y || undefined,
		'data-swiper-parallax-duration': attributes.parallax && attributes.parallax.duration || undefined
	});

  return saveElementProps;
};

addFilter( 'blocks.getSaveContent.extraProps', 'swiper/swiper-parallax/extra-props', addParallaxExtraProps );
