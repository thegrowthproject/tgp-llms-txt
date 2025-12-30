/**
 * WordPress Interactivity API store for Copy Button block.
 *
 * @package TGP_LLMs_Txt
 */

import { store, getContext } from '@wordpress/interactivity';

const { state, actions } = store( 'tgp/copy-button', {
	state: {
		get buttonText() {
			const ctx = getContext();
			switch ( ctx.copyState ) {
				case 'loading':
					return ctx.labelCopying;
				case 'success':
					return ctx.labelSuccess;
				case 'error':
					return ctx.labelError;
				default:
					return ctx.label;
			}
		},
		get isLoading() {
			const ctx = getContext();
			return ctx.copyState === 'loading';
		},
		get isDisabled() {
			const ctx = getContext();
			return ctx.copyState === 'loading';
		}
	},
	actions: {
		*copyMarkdown() {
			const ctx = getContext();

			// Prevent double-clicks.
			if ( ctx.copyState === 'loading' ) {
				return;
			}

			ctx.copyState = 'loading';

			try {
				const response = yield fetch( ctx.mdUrl );

				if ( ! response.ok ) {
					throw new Error( `HTTP ${ response.status }` );
				}

				const markdown = yield response.text();
				yield navigator.clipboard.writeText( markdown );

				ctx.copyState = 'success';
			} catch ( error ) {
				console.error( 'Copy failed:', error );
				ctx.copyState = 'error';
			}

			// Reset after 2 seconds.
			yield new Promise( ( resolve ) => setTimeout( resolve, 2000 ) );
			ctx.copyState = 'idle';
		}
	}
} );
