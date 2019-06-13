//  Import CSS.

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Internal dependencies
 */
import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import save from './save';


const { name, ...settings } = metadata;

registerBlockType(name, {
	...settings,
	title: __( 'Swiper Slide' ),
	parent: [ 'swiper/swiper' ],
	icon,
	description: __( 'A single item within a swiper block.' ),
	supports: {
		inserter: false,
		reusable: false,
		html: false,
	},
	getEditWrapperProps( attributes ) {
		const { width } = attributes;

		return {
			className: 'swiper-slide',
			tabIndex: '0'
		};

		/*
		if ( Number.isFinite( width ) ) {
			return {
				style: {
					flexBasis: width + '%',
				},
			};
		}*/
	},
	edit,
	save,
});
