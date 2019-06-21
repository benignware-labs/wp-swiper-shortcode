import { assign } from 'lodash';

import './parallax';

//  Import CSS.
// import './style.scss';
import './editor.scss';

import Swiper from '../../components/Swiper';
export { getSwiperControls } from './getSwiperControls';

import remoteSettings from '../../settings';


const { themes, options: { theme } } = remoteSettings;

Object.entries(themes).forEach((([ name, theme ]) => Swiper.themes.set(name, theme)));

Swiper.theme = theme;

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

export const SwiperBlockContext = React.createContext();

/**
 * Internal dependencies
 */
import deprecated from './deprecated';
import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import save from './save';

const { name, ...settings } = metadata;

registerBlockType( name, {
	...settings,
	title: __( 'Swiper' ),
	icon,
	description: __( 'Add a block that displays content in multiple slides, then add whatever content blocks you’d like.' ),
	supports: {
		align: [ 'wide', 'full' ],
		html: false,
	},
	// deprecated,
	edit,
	save,
	getEditWrapperProps( attributes ) {
		return {
			'data-swiper-block-edit-wrapper': true
		};
	},
});
