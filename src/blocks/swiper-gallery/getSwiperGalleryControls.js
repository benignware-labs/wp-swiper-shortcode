import classnames from 'classnames';
import { dropRight } from 'lodash';
import humanizeString from 'humanize-string';

import remoteSettings from '../../settings';
import Swiper from '../../components/Swiper';

import { SwiperBlockContext } from '.';
import { useState, useRef, useEffect } from 'react';


/**
 * WordPress dependencies
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;

const {
	withFilters,
	IconButton,
	PanelBody,
	PanelRow,
	RangeControl,
	SelectControl,
	ToggleControl,
	Toolbar,
	withNotices,
} = wp.components;


const { Fragment } = wp.element;

const {
	InspectorControls,
	InnerBlocks,
	BlockControls,
	BlockVerticalAlignmentToolbar,
} = wp.editor;


const { createBlock } = wp.blocks;

export const getSwiperGalleryControls = (props = {}, attributes = {}, size = 0) => {

	return (
    <PanelBody title={ __( 'Gallery Settings' ) }>
      <SelectControl
        label={__( 'Image Size' )}
        value={ attributes.size }
        options={ remoteSettings.sizes.map((size) => ({
          label: __(humanizeString(size)),
          value: size
        })) }
        onChange={ ( value ) => props.setAttributes({
          ...attributes,
          size: value
        })}
      />
      <SelectControl
        label={__( 'Image Fit' )}
        value={ attributes.fit }
        options={[{
          label: __('Auto'),
          value: 'auto',
        }, {
          label: __('Cover'),
          value: 'cover',
        }, {
          label: __('Contain'),
          value: 'contain',
        }, {
          label: __('Fill'),
          value: 'fill',
        }]}
        onChange={ ( value ) => props.setAttributes({
          ...attributes,
          fit: value
        })}
      />
    </PanelBody>
	);
}
