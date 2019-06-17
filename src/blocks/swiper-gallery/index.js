import { omit } from 'lodash';

//  Import CSS.
import './style.scss';
import './editor.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

import swiperMetadata from '../swiper/block.json';

import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import save from './save';
// import deprecated from './deprecated';
// import transforms from './transforms';

const { name, attributes, ...settings } = metadata;
const { attributes: swiperAttributes } = swiperMetadata;

registerBlockType( name, {
	...settings,
	attributes: omit({
		...attributes,
		...swiperAttributes
	}, [
		'parallax'
	]),
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Swiper Gallery' ), // Block title.
	icon,
	// transforms,
	// deprecated,
	edit,
	save
} );
