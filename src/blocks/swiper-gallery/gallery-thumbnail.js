/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
// import { Component } from '@wordpress/element';

const { Component } = wp.element;

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

		this.bindContainer = this.bindContainer.bind( this );

		this.state = {
		};
	}

	bindContainer( ref ) {
		this.container = ref;
	}

	componentDidUpdate( prevProps ) {
		const { isSelected, image, url } = this.props;
		if ( image && ! url ) {
			this.props.setAttributes( {
				url: image.source_url,
				alt: image.alt_text,
			} );
		}
	}

	render() {
		const { className, url, alt, id, linkTo, link, isFirstItem, isLastItem, setAttributes, 'aria-label': ariaLabel } = this.props;
		const { image, size } = this.props;
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

		const img = (
			// Disable reason: Image itself is not meant to be interactive, but should
			// direct image selection and unfocus caption fields.
			/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
			<div>
				{ src && (
					<img
						src={ src }
						alt={ alt }
						data-id={ id }
						// tabIndex="0"
						aria-label={ ariaLabel }
						ref={ this.bindContainer }
					/>
				)}
				{ isBlobURL( url ) && <Spinner /> }
			</div>
			/* eslint-enable jsx-a11y/no-noninteractive-element-interactions */
		);

		return (
			<div className={ classnames(
				className, {
				'is-transient': isBlobURL( url ),
			} ) }>
					{ href ? <a href={ href }>{ img }</a> : img }
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
