/**
 * External dependencies
 */
import classnames from 'classnames';
import { dropRight } from 'lodash';

import Swiper from '../../components/Swiper';

import { SwiperBlockContext } from '.';
import { useState, useRef, useEffect } from 'react';

import { getSwiperControls } from './getSwiperControls';
import remoteSettings from '../../settings';

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


/**
 * Internal dependencies
 */
import {
	getColumnsTemplate,
	hasExplicitColumnWidths,
	getMappedColumnWidths,
	getRedistributedColumnWidths,
	toWidthPrecision,
} from './utils';

/**
 * Allowed blocks constant is passed to InnerBlocks precisely as specified here.
 * The contents of the array should never change.
 * The array should contain the name of each block that is allowed.
 * In columns block, the only block we allow is 'swiper/swiper-slide'.
 *
 * @constant
 * @type {string[]}
*/
const ALLOWED_BLOCKS = [ 'swiper/swiper-slide' ];


export function ColumnsEdit( {
	attributes,
	className,
	addSlide,
	removeSlide,
	moveSlide,
	selectedBlock,
	innerBlocks,
	...props
} ) {
	const { columns, verticalAlignment } = attributes;
	const [ selectedClientId, setSelectedClientId ] = useState(null);
	const [ swiperInstance, setSwiperInstance ] = useState(null);

	const selectedIndex = selectedClientId ? innerBlocks.findIndex((block) => block.clientId === selectedClientId) : -1;
	const refContainer = useRef(null);

	const handleDocumentClick = event => {
    const { target } = event;
		const container = refContainer.current.closest('*[data-swiper-block-edit-wrapper]') || refContainer.current;

    if (!(target === container || container.contains(target))) {
			setSelectedClientId(null);
		}
  };

	useEffect(() => {
    window.addEventListener('click', handleDocumentClick);

    return () => {
      window.removeEventListener('click', handleDocumentClick);
    };
  });

	return (
		<SwiperBlockContext.Provider value={{
			selectedClientId,
			handleClick: (clientId) => setSelectedClientId(clientId)
		}}>
			<BlockControls>
				<Toolbar
					controls={[
						{
							icon: `plus-alt`,
							title: `Add slide`,
							onClick: () => {
								const slideIndex = selectedIndex >= 0 ? selectedIndex : innerBlocks.length;

								addSlide(selectedIndex);
								swiperInstance && swiperInstance.slideTo(slideIndex, 0);
							}
						},
						...(selectedClientId ? [{
							icon: `trash`,
							title: `Remove slide`,
							onClick: () => {
								removeSlide(selectedClientId);
								swiperInstance && swiperInstance.slideTo(Math.max(selectedIndex, innerBlocks.length - 1), 0);
							}
						}] : []),
						...(selectedClientId && (selectedIndex - 1 >= 0) ? [{
							icon: 'arrow-left',
							title: 'Move slide left',
							onClick: () => {
								moveSlide(selectedClientId, -1);
								swiperInstance && swiperInstance.slideTo(selectedIndex - 1, 0);
							}
						}] : []),
						...(selectedClientId && (selectedIndex + 1 < innerBlocks.length) ? [{
							icon: 'arrow-right',
							title: 'Move slide right',
							onClick: () => {
								moveSlide(selectedClientId, 1);
								swiperInstance && swiperInstance.slideTo(selectedIndex + 1, 0);
							}
						}] : [])
					]}
				>
				</Toolbar>
			</BlockControls>
			<InspectorControls>
				{getSwiperControls(props, attributes, innerBlocks.length)}
			</InspectorControls>
			<div ref={refContainer} className={className}>
				<Swiper
					{...attributes}
					autoplay={null}
					loop={false}
					selector=".editor-inner-blocks"
					wrapperSelector=".editor-block-list__layout"
					watchSlidesVisibility
					onInit={(swiperInstance) => {
						setSwiperInstance(swiperInstance);
					}}
					onDestroy={(swiperInstance) => setSwiperInstance(null)}
				>
					<InnerBlocks
						template={ getColumnsTemplate( Math.max(1, innerBlocks.length) ) }
						templateLock="all"
						allowedBlocks={ ALLOWED_BLOCKS } />
				</Swiper>
			</div>
		</SwiperBlockContext.Provider>
	);
}


export default compose(
	withSelect( ( select, ownProps ) => {
		let selectedBlock = null;
		let innerBlocks = null;

		const { clientId } = ownProps;
		const { getBlockOrder, getBlockRootClientId, getSelectedBlock, getBlockHierarchyRootClientId, getClientIdsOfDescendants, getSelectedBlockClientId } = select( 'core/block-editor' );
		const selectedBlockClientId = getSelectedBlockClientId();
		const block = select('core/editor').getBlocksByClientId(clientId)[0];

		innerBlocks = block ? block.innerBlocks : [];

		if (block && selectedBlockClientId) {
			for (let block of innerBlocks) {
				const descendants = getClientIdsOfDescendants(block);

				if (descendants.includes(selectedBlockClientId)) {
					selectedBlock = block;
					break;
				}
			}
		}

		return {
			selectedBlock,
			innerBlocks
		};
	} ),
	withDispatch( ( dispatch, ownProps, registry ) => ({
		addSlide(index = -1) {
			const { clientId, setAttributes, attributes } = ownProps;
			const { replaceInnerBlocks } = dispatch( 'core/block-editor' );
			const { getBlocks } = registry.select( 'core/block-editor' );

			let innerBlocks = getBlocks( clientId );

			const createProps = {
				// className: 'swiper-slide',
			};

			if (index >= 0) {
				innerBlocks = [ ...innerBlocks ];
				innerBlocks.splice(index, 0, createBlock('swiper/swiper-slide', createProps));
			} else {
				innerBlocks = [
					...innerBlocks,
					createBlock('swiper/swiper-slide', createProps),
				];
			}

			replaceInnerBlocks( clientId, innerBlocks, true );
		},

		removeSlide(blockId) {
			const { clientId, setAttributes, attributes } = ownProps;
			const { replaceInnerBlocks } = dispatch('core/block-editor');
			const { getBlocks } = registry.select('core/block-editor');

			let innerBlocks = getBlocks(clientId);

			innerBlocks = innerBlocks.filter((block) => block.clientId !== blockId);

			replaceInnerBlocks(clientId, innerBlocks, true);
		},

		moveSlide(blockId, indexIncrement) {
			const { clientId, setAttributes, attributes } = ownProps;
			const { replaceInnerBlocks } = dispatch('core/block-editor');
			const { getBlocks } = registry.select('core/block-editor');

			let innerBlocks = getBlocks( clientId );

			const blockIndex = innerBlocks.findIndex((block) => block.clientId === blockId);
			const block = innerBlocks[blockIndex];
			const nextIndex = blockIndex + indexIncrement;

			if (nextIndex < innerBlocks.length && nextIndex >= 0) {
				innerBlocks = innerBlocks.filter((block) => block.clientId !== blockId)

				innerBlocks.splice(nextIndex, 0, block);

				replaceInnerBlocks( clientId, innerBlocks, true );
			}
		},
	}))
)(ColumnsEdit);
