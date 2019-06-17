/**
 * WordPress dependencies
 */
// import { RichText } from '@wordpress/block-editor';
import Swiper from '../../components/Swiper';

const { RichText } = wp.editor;
/**
 * Internal dependencies
 */
import { defaultColumnsNumber } from './shared';

export default function save( { attributes } ) {
	const {
		images,
		columns = defaultColumnsNumber( attributes ),
		imageCrop,
		linkTo,
		navigation,
		fit
	} = attributes;

	return (
		<div className={ `columns-${ columns } ${ imageCrop ? 'is-cropped' : '' }` } >
			<Swiper
				navigation={navigation}
				slidesPerView={columns}
				{...Swiper.getOptions(attributes)}
			>
				{ images.map( ( image ) => {
					let href;

					switch ( linkTo ) {
						case 'media':
							href = image.url;
							break;
						case 'attachment':
							href = image.link;
							break;
					}

					const img = (
						<img
							className="swiper-gallery-img"
							style={fit ? {
								width: '100%',
								height: '100%',
								objectFit: fit,
								objectPosition: 'center'
							} : undefined}
							src={ image.url }
							alt={ image.alt }
							data-id={ image.id }
							data-link={ image.link }
							className={ image.id ? `wp-image-${ image.id }` : null }
						/>
					);

					return (
						<div key={ image.id || image.url } className="blocks-gallery-item">
							{ href ? <a href={ href }>{ img }</a> : img }
							{ image.caption && image.caption.length > 0 && (
								<RichText.Content tagName="figcaption" value={ image.caption } />
							) }
						</div>
					);
				} ) }
			</Swiper>
		</div>
	);
}
