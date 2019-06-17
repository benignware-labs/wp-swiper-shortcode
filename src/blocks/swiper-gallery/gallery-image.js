/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
// import { Component } from '@wordpress/element';

const { Component, Fragment } = wp.element;

// import { IconButton, Spinner } from '@wordpress/components';

const { IconButton, Spinner } = wp.components;

// import { __ } from '@wordpress/i18n';

const { __ } = wp.i18n;

// import { BACKSPACE, DELETE } from '@wordpress/keycodes';

const { BACKSPACE, DELETE } = wp.keycodes;

// import { withSelect } from '@wordpress/data';

const { withSelect } = wp.data;

// import { RichText } from '@wordpress/block-editor';

const { RichText } = wp.editor;

// import { isBlobURL } from '@wordpress/blob';

const { isBlobURL } = wp.blob;

class GalleryImage extends Component {
	constructor() {
		super( ...arguments );

		this.onSelectImage = this.onSelectImage.bind( this );
		this.onSelectCaption = this.onSelectCaption.bind( this );
		this.onRemoveImage = this.onRemoveImage.bind( this );
		this.bindContainer = this.bindContainer.bind( this );

		this.state = {
			captionSelected: false,
		};
	}

	bindContainer( ref ) {
		this.container = ref;
	}

	onSelectCaption() {
		if ( ! this.state.captionSelected ) {
			this.setState( {
				captionSelected: true,
			} );
		}

		if ( ! this.props.isSelected ) {
			this.props.onSelect();
		}
	}

	onSelectImage() {
		if ( ! this.props.isSelected ) {
			this.props.onSelect();
		}

		if ( this.state.captionSelected ) {
			this.setState( {
				captionSelected: false,
			} );
		}
	}

	onRemoveImage( event ) {
		if (
			this.container === document.activeElement &&
			this.props.isSelected && [ BACKSPACE, DELETE ].indexOf( event.keyCode ) !== -1
		) {
			event.stopPropagation();
			event.preventDefault();
			this.props.onRemove();
		}
	}

	componentDidUpdate( prevProps ) {
		const { isSelected, image, url } = this.props;
		if ( image && ! url ) {
			this.props.setAttributes( {
				url: image.source_url,
				alt: image.alt_text,
			} );
		}

		// unselect the caption so when the user selects other image and comeback
		// the caption is not immediately selected
		if ( this.state.captionSelected && ! isSelected && prevProps.isSelected ) {
			this.setState( {
				captionSelected: false,
			} );
		}
	}

	render() {
		const { className, url, alt, id, linkTo, link, isFirstItem, isLastItem, isSelected, caption, onRemove, onMoveForward, onMoveBackward, setAttributes, 'aria-label': ariaLabel } = this.props;
		const { image, size, fit } = this.props;
		const sizes = image && image.media_details.sizes;
		const src = size ? sizes && sizes[size] ? sizes[size].source_url : null : url;

		let href;

		switch ( linkTo ) {
			case 'media':
				href = url;
				break;
			case 'attachment':
				href = link;
				break;
		}

		console.log('PROPs..', fit, this.props);

		const img = (
			// Disable reason: Image itself is not meant to be interactive, but should
			// direct image selection and unfocus caption fields.
			/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
			<Fragment>
				{ src && (
					<img
						className="swiper-gallery-img"
						style={fit ? {
							width: '100%',
							height: '100%',
							objectFit: fit,
							objectPosition: 'center'
						} : undefined}
						src={ src }
						alt={ alt }
						data-id={ id }
						onClick={ this.onSelectImage }
						onFocus={ this.onSelectImage }
						onKeyDown={ this.onRemoveImage }
						// tabIndex="0"
						aria-label={ ariaLabel }
						ref={ this.bindContainer }
					/>
				)}
				{ isBlobURL( url ) && <Spinner /> }
			</Fragment>
			/* eslint-enable jsx-a11y/no-noninteractive-element-interactions */
		);

		return (
			<div className={ classnames(
				className, {
				'is-selected': isSelected,
				'is-transient': isBlobURL( url ),
			} ) }>
					{ href ? <a href={ href }>{ img }</a> : img }
					{/*
					<div className="block-library-gallery-item__move-menu">
						<IconButton
							icon="arrow-left"
							onClick={ isFirstItem ? undefined : onMoveBackward }
							className="blocks-gallery-item__move-backward"
							label={ __( 'Move Image Backward' ) }
							aria-disabled={ isFirstItem }
							disabled={ ! isSelected }
						/>
						<IconButton
							icon="arrow-right"
							onClick={ isLastItem ? undefined : onMoveForward }
							className="blocks-gallery-item__move-forward"
							label={ __( 'Move Image Forward' ) }
							aria-disabled={ isLastItem }
							disabled={ ! isSelected }
						/>
					</div>
					<div className="block-library-gallery-item__inline-menu">
						<IconButton
							icon="no-alt"
							onClick={ onRemove }
							className="blocks-gallery-item__remove"
							label={ __( 'Remove Image' ) }
							disabled={ ! isSelected }
						/>
					</div>
					*/}
					{/*
					<RichText
						tagName="figcaption"
						placeholder={ isSelected ? __( 'Write caption…' ) : null }
						value={ caption }
						isSelected={ this.state.captionSelected }
						onChange={ ( newCaption ) => setAttributes( { caption: newCaption } ) }
						unstableOnFocus={ this.onSelectCaption }
						inlineToolbar
					/>
					*/}
			</div>
		);
	}
}

export default withSelect( ( select, ownProps ) => {
	const { getMedia } = select( 'core' );
	const { id } = ownProps;

	const result = {
		image: id ? getMedia( id ) : null,
	};

	return result;
} )( GalleryImage );
