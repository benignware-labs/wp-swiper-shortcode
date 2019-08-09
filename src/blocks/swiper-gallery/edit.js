/**
 * External dependencies
 */
import classnames from 'classnames';
import { filter, map } from 'lodash';
import Swiper from '../../components/Swiper';
import remoteSettings from '../../settings';

import { getSwiperControls } from '../swiper';
import { getSwiperGalleryControls } from './getSwiperGalleryControls';

/**
 * Internal dependencies
 */
import GalleryImage from './gallery-image';
import GalleryThumbnail from './gallery-thumbnail';
import icon from './icon';
import { defaultColumnsNumber, pickRelevantMediaFiles } from './shared';

/**
 * WordPress dependencies
 */
/*import {
	IconButton,
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
	Toolbar,
	withNotices,
} from '@wordpress/components';
*/

const {
	IconButton,
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
	Toolbar,
	withNotices,
} = wp.components;

/*
import {
	BlockControls,
	BlockIcon,
	MediaPlaceholder,
	MediaUpload,
	InspectorControls,
} from '@wordpress/block-editor';
*/

const {
	BlockControls,
	BlockIcon,
	MediaPlaceholder,
	MediaUpload,
	InspectorControls,
} = wp.editor;

// import { Component } from '@wordpress/element';

const { Component } = wp.element;

// import { __, sprintf } from '@wordpress/i18n';

const { __, sprintf } = wp.i18n; // Import __() from wp.i18n



const MAX_COLUMNS = 8;
const MAX_SLIDES_PER_VIEW = 8;
const linkOptions = [
	{ value: 'attachment', label: __( 'Attachment Page' ) },
	{ value: 'media', label: __( 'Media File' ) },
	{ value: 'none', label: __( 'None' ) },
];
const ALLOWED_MEDIA_TYPES = [ 'image', 'video' ];


class GalleryEdit extends Component {
	constructor() {
		super( ...arguments );

		this.onSelectImage = this.onSelectImage.bind( this );
		this.onSelectImages = this.onSelectImages.bind( this );
		this.setLinkTo = this.setLinkTo.bind( this );
		this.setColumnsNumber = this.setColumnsNumber.bind( this );
		this.toggleImageCrop = this.toggleImageCrop.bind( this );
		this.onMove = this.onMove.bind( this );
		this.onMoveForward = this.onMoveForward.bind( this );
		this.onMoveBackward = this.onMoveBackward.bind( this );
		this.onRemoveImage = this.onRemoveImage.bind( this );
		this.setImageAttributes = this.setImageAttributes.bind( this );
		this.setAttributes = this.setAttributes.bind( this );

		this.state = {
			selectedImage: null,
		};
	}

	setAttributes( attributes ) {
		if ( attributes.ids ) {
			throw new Error( 'The "ids" attribute should not be changed directly. It is managed automatically when "images" attribute changes' );
		}

		if ( attributes.images ) {
			attributes = {
				...attributes,
				ids: map( attributes.images, 'id' ),
			};
		}

		this.props.setAttributes( attributes );
	}

	onSelectImage( index ) {
		return () => {
			if ( this.state.selectedImage !== index ) {
				this.setState( {
					selectedImage: index,
				} );
			}
		};
	}

	onMove( oldIndex, newIndex ) {
		const images = [ ...this.props.attributes.images ];
		images.splice( newIndex, 1, this.props.attributes.images[ oldIndex ] );
		images.splice( oldIndex, 1, this.props.attributes.images[ newIndex ] );
		this.setState( { selectedImage: newIndex } );
		this.setAttributes( { images } );
	}

	onMoveForward( oldIndex ) {
		return () => this.moveForward(oldIndex);
	}

	moveForward(oldIndex) {
		if ( oldIndex === this.props.attributes.images.length - 1 ) {
			return;
		}
		this.onMove( oldIndex, oldIndex + 1 );
	}

	onMoveBackward( oldIndex ) {
		return () => this.moveBackward(oldIndex);
	}

	moveBackward(oldIndex) {
		if ( oldIndex === 0 ) {
			return;
		}
		this.onMove( oldIndex, oldIndex - 1 );
	}

	onRemoveImage( index ) {
		return () => {
			return this.removeImage(index);
		}
	}

	removeImage(index) {
		const images = filter( this.props.attributes.images, ( img, i ) => index !== i );
		const { columns } = this.props.attributes;
		this.setState( { selectedImage: null } );
		this.setAttributes( {
			images,
			columns: columns ? Math.min( images.length, columns ) : columns,
		} );
	}

	onSelectImages( images ) {
		const { columns } = this.props.attributes;
		this.setAttributes( {
			images: images.map( ( image ) => pickRelevantMediaFiles( image ) ),
			columns: columns ? Math.min( images.length, columns ) : columns,
		} );
	}

	setLinkTo( value ) {
		this.setAttributes({ linkTo: value });
	}

	setColumnsNumber( value ) {
		this.setAttributes({ columns: value });
	}

	toggleImageCrop() {
		this.setAttributes({ imageCrop: ! this.props.attributes.imageCrop });
	}

	getImageCropHelp( checked ) {
		return checked ? __( 'Thumbnails are cropped to align.' ) : __( 'Thumbnails are not cropped.' );
	}

	setSlidesPerViewNumber = (value) => this.setAttributes({ slides_per_view: value });

	toggleNavigation = () => this.setAttributes({ navigation: ! this.props.attributes.navigation });
	getNavigationHelp = (checked) => checked ? __( 'Navigation is displayed' ) : __( 'Navigation is not displayed.' );

	setImageAttributes( index, attributes ) {
		const { attributes: { images } } = this.props;
		const { setAttributes } = this;
		if ( ! images[ index ] ) {
			return;
		}
		setAttributes( {
			images: [
				...images.slice( 0, index ),
				{
					...images[ index ],
					...attributes,
				},
				...images.slice( index + 1 ),
			],
		} );
	}

	componentDidUpdate( prevProps ) {
		// Deselect images when deselecting the block
		if ( ! this.props.isSelected && prevProps.isSelected ) {
			this.setState( {
				selectedImage: null,
				captionSelected: false,
			} );
		}
	}

	render() {
		const { clientId, attributes, isSelected, className, noticeOperations, noticeUI } = this.props;
		const { selectedImage, swiperInstance } = this.state;
		const {
			images,
			columns = defaultColumnsNumber( attributes ),
			slidesPerView = Math.min(3, images.length),
			align,
			imageCrop,
			linkTo,
			navigation,
			size,
			thumbs,
			fit
		} = attributes;

		const hasImages = !! images.length;

		const controls = (
			<BlockControls>
				{ hasImages && (
					<Toolbar
						controls={[
							...(typeof selectedImage === 'number' && selectedImage >= 0 ? [{
								icon: `trash`,
								title: `Remove slide`,
								onClick: () => this.removeImage(selectedImage)
							}] : []),
							...(typeof selectedImage === 'number' && selectedImage > 0 ? [{
								icon: 'arrow-left',
								title: `Move slide backward`,
								onClick: () => {
									this.moveBackward(selectedImage);
									swiperInstance && swiperInstance.slideTo(selectedImage - 1, 0);
								}
							}] : []),
							...(typeof selectedImage === 'number' && selectedImage < images.length - 1 ? [{
								icon: 'arrow-right',
								title: `Move slide forward`,
								onClick: () => {
									this.moveForward(selectedImage);
									swiperInstance && swiperInstance.slideTo(selectedImage + 1, 0);
								}
							}] : [])
						]}
					>
						<MediaUpload
							onSelect={ this.onSelectImages }
							allowedTypes={ ALLOWED_MEDIA_TYPES }
							multiple
							gallery
							value={ images.map( ( img ) => img.id ) }
							render={ ( { open } ) => (
								<IconButton
									className="components-toolbar__control"
									label={ __( 'Edit gallery' ) }
									icon="edit"
									onClick={ open }
								/>
							) }
						/>
					</Toolbar>
				) }
			</BlockControls>
		);

		const mediaPlaceholder = (
			<MediaPlaceholder
				addToGallery={ hasImages }
				isAppender={ hasImages }
				className={ className }
				dropZoneUIOnly={ hasImages && ! isSelected }
				icon={ ! hasImages && <BlockIcon icon={ icon } /> }
				labels={ {
					title: ! hasImages && __( 'Gallery' ),
					instructions: ! hasImages && __( 'Drag images, upload new ones or select files from your library.' ),
				} }
				onSelect={ this.onSelectImages }
				accept="image/*"
				allowedTypes={ ALLOWED_MEDIA_TYPES }
				multiple
				value={ hasImages ? images : undefined }
				onError={ noticeOperations.createErrorNotice }
				notices={ hasImages ? undefined : noticeUI }
			/>
		);

		if ( ! hasImages ) {
			return (
				<div>
					{ controls }
					{ mediaPlaceholder }
				</div>
			);
		}

		const id = 'swiper-' + clientId;
		const thumbsId = `${id}-thumbs`;

		const options = {
			...attributes
		};

		if (options.thumbs) {
			options.thumbs = {
				swiper: `#${thumbsId}`
			};
		}

		return (
			<div>
				{ controls }
					<InspectorControls>
					{getSwiperGalleryControls(this.props, attributes, images.length)}
					{getSwiperControls(this.props, attributes, images.length)}
					{/*
					<PanelBody title={ __( 'Gallery Settings' ) }>
						{ images.length > 1 && <RangeControl
							label={ __( 'Columns' ) }
							value={ columns }
							onChange={ this.setColumnsNumber }
							min={ 1 }
							max={ Math.min( MAX_COLUMNS, images.length ) }
							required
						/> }
						<ToggleControl
							label={ __( 'Crop Images' ) }
							checked={ !! imageCrop }
							onChange={ this.toggleImageCrop }
							help={ this.getImageCropHelp }
						/>
						<SelectControl
							label={ __( 'Link To' ) }
							value={ linkTo }
							onChange={ this.setLinkTo }
							options={ linkOptions }
						/>
					</PanelBody>
					*/}
				</InspectorControls>
				{ noticeUI }
				<div
					className={ classnames(
						className,
						{
							[ `align${ align }` ]: align,
							[ `columns-${ columns }` ]: columns,
							// [ `slidesPerView-${ slidesPerView }` ]: slidesPerView,
							'is-cropped': imageCrop,
						}
					) }
				>
					{thumbs && (
						<Swiper
							id={thumbsId}
							navigation={null}
							scrollbar={null}
							pagination={null}
							{...{
								slidesPerView: 4,
								freeMode: true,
								watchSlidesVisibility: true,
								watchSlidesProgress: true,
								...(thumbs || {})
							}}
							style={{ order: 1 }}
						>
							{images.map( ( img, index ) => {
								const { size } = thumbs;

								return (
									<GalleryThumbnail
										className={classnames(
											'swiper-gallery-thumbnail'
										)}
										key={ img.id || img.url }
										size={thumbs.size || 'thumbnail'}
										url={ img.url }
										alt={ img.alt }
										id={ img.id }
									/>
								);
							})}
						</Swiper>
					)}
					<Swiper
						{...options}
						id={id}
						autoplay={null}
						loop={false}
						onInit={(swiperInstance) => this.setState({ swiperInstance })}
						onDestroy={() => this.setState({ swiperInstance: null })}
					>
						{ images.map( ( img, index ) => {
							/* translators: %1$d is the order number of the image, %2$d is the total number of images. */
							const ariaLabel = sprintf( __( 'image %1$d of %2$d in gallery' ), ( index + 1 ), images.length );

							return (
								<GalleryImage
									fit={fit}
									className={classnames(
										'swiper-gallery-item',
										isSelected && selectedImage === index && 'is-selected'
									)}
									key={ img.id || img.url }
									size={size}
									url={ img.url }
									alt={ img.alt }
									id={ img.id }
									isFirstItem={ index === 0 }
									isLastItem={ ( index + 1 ) === images.length }
									isSelected={ isSelected && selectedImage === index }
									onMoveBackward={ this.onMoveBackward( index ) }
									onMoveForward={ this.onMoveForward( index ) }
									onRemove={ this.onRemoveImage( index ) }
									onSelect={ this.onSelectImage( index ) }
									setAttributes={ ( attrs ) => this.setImageAttributes( index, attrs ) }
									caption={ img.caption }
									aria-label={ ariaLabel }
								/>
							);
						} ) }
					</Swiper>
				</div>
				{ !hasImages && mediaPlaceholder }
			</div>
		);
	}
}

export default withNotices( GalleryEdit );
