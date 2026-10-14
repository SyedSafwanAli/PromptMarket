/**
 * PromptAtlas — copy, Recently Copied, and Saved.
 *
 * Everything here is client-side. Nothing is sent to the server except a
 * read-only lookup of which stored prompt IDs still exist, and that request
 * carries only IDs the visitor's own browser already stored.
 *
 * ---------------------------------------------------------------------------
 * STORAGE — exactly what is kept, where, and for how long
 * ---------------------------------------------------------------------------
 * One localStorage key, `promptatlas:store`, holding:
 *
 *   { "v": 1,
 *     "recent": [ { "id": 6, "t": 1760000000 }, … ],
 *     "saved":  [ { "id": 7, "t": 1760000000 }, … ] }
 *
 *   v       schema version, so a future change can migrate or discard rather
 *           than crash on data it does not understand
 *   id      the prompt's WordPress post ID
 *   t       a unix timestamp in seconds, used only for ordering
 *
 * Nothing else. No prompt text is duplicated into storage, no identifier for
 * the visitor is created, and nothing is sent anywhere. It stays in that one
 * browser until the visitor clears it or clears their site data. Recently
 * Copied is capped at RECENT_LIMIT entries; Saved is capped at SAVED_LIMIT.
 *
 * This description is what the Phase 17 privacy policy must say. If this
 * changes, that policy changes.
 * ---------------------------------------------------------------------------
 *
 * Progressive enhancement: the prompt text is plain selectable text in the
 * page. Every control here is hidden by CSS until this script runs, so nothing
 * is ever shown as working when it cannot.
 */
( function () {
	'use strict';

	var STORAGE_KEY = 'promptatlas:store';
	var SCHEMA_VERSION = 1;
	var RECENT_LIMIT = 20;
	var SAVED_LIMIT = 100;
	var RESET_AFTER = 2200;

	/* ------------------------------------------------------------------ */
	/* Storage                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * An empty, valid store.
	 *
	 * @return {Object} Empty store.
	 */
	function emptyStore() {
		return { v: SCHEMA_VERSION, recent: [], saved: [] };
	}

	/**
	 * Read the store, tolerating every way it can be unusable.
	 *
	 * localStorage throws outright in some private-browsing modes and whenever
	 * site data is blocked, and the stored value can be corrupted by anything
	 * from a failed write to a user editing it by hand. In every one of those
	 * cases the correct behaviour is to carry on with an empty store rather
	 * than to break the page.
	 *
	 * @return {Object} A valid store, possibly empty.
	 */
	function readStore() {
		var raw;

		try {
			raw = window.localStorage.getItem( STORAGE_KEY );
		} catch ( e ) {
			return emptyStore();
		}

		if ( ! raw ) {
			return emptyStore();
		}

		var parsed;

		try {
			parsed = JSON.parse( raw );
		} catch ( e ) {
			// Corrupted beyond use. Discard rather than guess.
			return emptyStore();
		}

		if ( ! parsed || typeof parsed !== 'object' || parsed.v !== SCHEMA_VERSION ) {
			// Unknown or future schema version: discard rather than
			// misinterpret. This is why `v` exists.
			return emptyStore();
		}

		return {
			v: SCHEMA_VERSION,
			recent: sanitiseList( parsed.recent, RECENT_LIMIT ),
			saved: sanitiseList( parsed.saved, SAVED_LIMIT )
		};
	}

	/**
	 * Coerce a stored list into valid entries.
	 *
	 * @param {*}      list  Whatever was stored.
	 * @param {number} limit Maximum entries to keep.
	 * @return {Array} Valid entries.
	 */
	function sanitiseList( list, limit ) {
		if ( ! Array.isArray( list ) ) {
			return [];
		}

		var clean = [];
		var seen = {};

		for ( var i = 0; i < list.length && clean.length < limit; i++ ) {
			var entry = list[ i ];

			if ( ! entry || typeof entry !== 'object' ) {
				continue;
			}

			var id = parseInt( entry.id, 10 );

			if ( ! id || id < 1 || seen[ id ] ) {
				continue;
			}

			seen[ id ] = true;

			var t = parseInt( entry.t, 10 );

			clean.push( { id: id, t: isNaN( t ) ? 0 : t } );
		}

		return clean;
	}

	/**
	 * Write the store. Failure is silent and non-fatal by design.
	 *
	 * @param {Object} store The store.
	 * @return {boolean} True when it was written.
	 */
	function writeStore( store ) {
		try {
			window.localStorage.setItem( STORAGE_KEY, JSON.stringify( store ) );
			return true;
		} catch ( e ) {
			// Quota exceeded, or storage blocked. The action still worked for
			// this page view; it just will not be remembered.
			return false;
		}
	}

	/**
	 * Add an ID to the front of a list, de-duplicating and trimming.
	 *
	 * @param {Array}  list  The list.
	 * @param {number} id    Prompt ID.
	 * @param {number} limit Maximum length.
	 * @return {Array} The updated list.
	 */
	function promote( list, id, limit ) {
		var filtered = list.filter( function ( entry ) {
			return entry.id !== id;
		} );

		filtered.unshift( { id: id, t: Math.floor( Date.now() / 1000 ) } );

		return filtered.slice( 0, limit );
	}

	/* ------------------------------------------------------------------ */
	/* Copy                                                                */
	/* ------------------------------------------------------------------ */

	/**
	 * Copy text, falling back to selecting it when the clipboard is unavailable.
	 *
	 * navigator.clipboard does not exist in a non-secure context, which includes
	 * plain http:// on anything but localhost — so the fallback is what runs on
	 * an http staging site, not a theoretical branch. It also rejects when the
	 * user denies clipboard permission, which lands in the same place.
	 *
	 * On failure the text is left selected, so the visitor can press Ctrl+C —
	 * which is exactly what the failure message tells them to do.
	 *
	 * @param {string}      text   Text to copy.
	 * @param {HTMLElement} source Element holding the text.
	 * @return {Promise<boolean>} Whether the copy succeeded.
	 */
	function copyText( text, source ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text ).then(
				function () {
					return true;
				},
				function () {
					return legacyCopy( source );
				}
			);
		}

		return Promise.resolve( legacyCopy( source ) );
	}

	/**
	 * Select the element's text and ask the document to copy it.
	 *
	 * @param {HTMLElement} source Element holding the text.
	 * @return {boolean} Whether the copy command reported success.
	 */
	function legacyCopy( source ) {
		if ( ! source ) {
			return false;
		}

		try {
			var range = document.createRange();
			range.selectNodeContents( source );

			var selection = window.getSelection();
			selection.removeAllRanges();
			selection.addRange( range );

			var ok = document.execCommand( 'copy' );

			if ( ok ) {
				selection.removeAllRanges();
			}

			return ok;
		} catch ( e ) {
			return false;
		}
	}

	/**
	 * Wire up a copy button.
	 *
	 * @param {HTMLElement} button The button.
	 */
	function initCopyButton( button ) {
		if ( button.getAttribute( 'data-pa-bound' ) === '1' ) {
			return; // Guard against double binding.
		}

		button.setAttribute( 'data-pa-bound', '1' );

		var targetId = button.getAttribute( 'data-copy-target' );
		var target = targetId ? document.getElementById( targetId ) : null;

		if ( ! target ) {
			button.remove();
			return;
		}

		var promptId = parseInt( button.getAttribute( 'data-prompt-id' ), 10 ) || 0;
		var labelEl = button.querySelector( '.js-copy-button-label' );
		var block = button.closest( '.prompt-block' );
		var status = block ? block.querySelector( '.prompt-block__status' ) : null;

		var labelDefault = button.getAttribute( 'data-label-default' ) || 'Copy prompt';
		var labelCopied = button.getAttribute( 'data-label-copied' ) || 'Copied';
		var labelFailed = button.getAttribute( 'data-label-failed' ) || 'Press Ctrl+C to copy';

		var resetTimer = null;
		var busy = false;

		button.addEventListener( 'click', function () {
			if ( busy ) {
				return; // Prevents a double-click queueing two copies.
			}

			busy = true;

			// innerText, not textContent: the placeholder tokens are wrapped in
			// spans, and innerText returns the rendered text with line breaks
			// intact, which is what the visitor sees and expects to paste.
			var text = target.innerText || target.textContent || '';

			copyText( text, target ).then( function ( ok ) {
				busy = false;

				if ( labelEl ) {
					labelEl.textContent = ok ? labelCopied : labelFailed;
				}

				button.setAttribute( 'data-state', ok ? 'copied' : 'failed' );

				// The visible label changing is not announced by a screen
				// reader; the live region states the outcome explicitly.
				if ( status ) {
					status.textContent = ok ? labelCopied : labelFailed;
				}

				if ( ok && promptId ) {
					var store = readStore();
					store.recent = promote( store.recent, promptId, RECENT_LIMIT );
					writeStore( store );
				}

				window.clearTimeout( resetTimer );
				resetTimer = window.setTimeout( function () {
					if ( labelEl ) {
						labelEl.textContent = labelDefault;
					}
					button.removeAttribute( 'data-state' );
					if ( status ) {
						status.textContent = '';
					}
				}, RESET_AFTER );
			} );
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Save                                                                */
	/* ------------------------------------------------------------------ */

	/**
	 * Wire up a save toggle.
	 *
	 * @param {HTMLElement} button The button.
	 */
	function initSaveButton( button ) {
		if ( button.getAttribute( 'data-pa-bound' ) === '1' ) {
			return;
		}

		button.setAttribute( 'data-pa-bound', '1' );

		var promptId = parseInt( button.getAttribute( 'data-prompt-id' ), 10 ) || 0;

		if ( ! promptId ) {
			button.remove();
			return;
		}

		var labelEl = button.querySelector( '.js-save-button-label' );
		var labelSave = button.getAttribute( 'data-label-save' ) || 'Save';
		var labelSaved = button.getAttribute( 'data-label-saved' ) || 'Saved';

		/**
		 * Reflect the current state onto the button.
		 *
		 * @param {boolean} isSaved Whether the prompt is saved.
		 */
		function render( isSaved ) {
			button.setAttribute( 'aria-pressed', isSaved ? 'true' : 'false' );
			button.setAttribute( 'data-state', isSaved ? 'saved' : 'unsaved' );

			if ( labelEl ) {
				labelEl.textContent = isSaved ? labelSaved : labelSave;
			}
		}

		var store = readStore();

		render(
			store.saved.some( function ( entry ) {
				return entry.id === promptId;
			} )
		);

		button.addEventListener( 'click', function () {
			var current = readStore();

			var isSaved = current.saved.some( function ( entry ) {
				return entry.id === promptId;
			} );

			if ( isSaved ) {
				current.saved = current.saved.filter( function ( entry ) {
					return entry.id !== promptId;
				} );
			} else {
				current.saved = promote( current.saved, promptId, SAVED_LIMIT );
			}

			writeStore( current );
			render( ! isSaved );
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Lists                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Render a stored list into a container.
	 *
	 * Resolves the stored IDs against the server so that entries pointing at
	 * deleted or unpublished prompts vanish instead of becoming dead links, and
	 * prunes them from storage at the same time.
	 *
	 * @param {HTMLElement} container The list container.
	 * @param {string}      which     Either 'recent' or 'saved'.
	 */
	function renderList( container, which ) {
		var store = readStore();
		var entries = store[ which ] || [];

		var emptyEl = container.querySelector( '.js-list-empty' );
		var listEl = container.querySelector( '.js-list-items' );
		var clearEl = container.querySelector( '.js-list-clear' );

		/**
		 * Show the empty state.
		 */
		function showEmpty() {
			if ( listEl ) {
				listEl.innerHTML = '';
				listEl.hidden = true;
			}
			if ( emptyEl ) {
				emptyEl.hidden = false;
			}
			if ( clearEl ) {
				clearEl.hidden = true;
			}
		}

		if ( ! entries.length ) {
			showEmpty();
			return;
		}

		var ids = entries.map( function ( entry ) {
			return entry.id;
		} );

		var endpoint = container.getAttribute( 'data-endpoint' );

		if ( ! endpoint ) {
			showEmpty();
			return;
		}

		fetch( endpoint + '?ids=' + encodeURIComponent( ids.join( ',' ) ), {
			credentials: 'same-origin'
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'lookup failed' );
				}
				return response.json();
			} )
			.then( function ( data ) {
				var found = ( data && Array.isArray( data.prompts ) ) ? data.prompts : [];

				var byId = {};
				found.forEach( function ( item ) {
					byId[ item.id ] = item;
				} );

				// Keep the visitor's own ordering; drop anything the server did
				// not return, and write the pruned list back.
				var ordered = [];
				var survivors = [];

				entries.forEach( function ( entry ) {
					if ( byId[ entry.id ] ) {
						ordered.push( byId[ entry.id ] );
						survivors.push( entry );
					}
				} );

				if ( survivors.length !== entries.length ) {
					var pruned = readStore();
					pruned[ which ] = survivors;
					writeStore( pruned );
				}

				if ( ! ordered.length ) {
					showEmpty();
					return;
				}

				if ( emptyEl ) {
					emptyEl.hidden = true;
				}
				if ( clearEl ) {
					clearEl.hidden = false;
				}

				if ( ! listEl ) {
					return;
				}

				listEl.innerHTML = '';
				listEl.hidden = false;

				ordered.forEach( function ( item ) {
					var li = document.createElement( 'li' );
					li.className = 'stored-list__item';

					if ( item.category ) {
						var cat = document.createElement( 'span' );
						cat.className = 'stored-list__category';
						// textContent, never innerHTML: the title and category
						// come from the server as plain text and must not be
						// parsed as markup.
						cat.textContent = item.category;
						li.appendChild( cat );
					}

					var link = document.createElement( 'a' );
					link.className = 'stored-list__link';
					link.href = item.url;
					link.textContent = item.title;
					li.appendChild( link );

					listEl.appendChild( li );
				} );
			} )
			.catch( function () {
				showEmpty();
			} );

		if ( clearEl ) {
			clearEl.addEventListener( 'click', function () {
				var current = readStore();
				current[ which ] = [];
				writeStore( current );
				showEmpty();
			} );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Boot                                                                */
	/* ------------------------------------------------------------------ */

	var copyButtons = document.querySelectorAll( '.js-copy-button' );
	for ( var i = 0; i < copyButtons.length; i++ ) {
		initCopyButton( copyButtons[ i ] );
	}

	var saveButtons = document.querySelectorAll( '.js-save-button' );
	for ( var j = 0; j < saveButtons.length; j++ ) {
		initSaveButton( saveButtons[ j ] );
	}

	var lists = document.querySelectorAll( '.js-stored-list' );
	for ( var k = 0; k < lists.length; k++ ) {
		renderList( lists[ k ], lists[ k ].getAttribute( 'data-list' ) );
	}
} )();
