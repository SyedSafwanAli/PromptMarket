/**
 * Copy-to-clipboard for the prompt block.
 *
 * Scope note: Phase 10 owns "Recently Copied" and "Saved prompts". This file
 * deliberately implements only the copy action itself, because the standing
 * project rule forbids shipping a feature that exists as a non-functional
 * visual placeholder — a copy button that does not copy would be exactly that.
 *
 * Progressive enhancement: the button is hidden by CSS until this script runs
 * (.promptatlas-no-js .copy-button { display: none }), so a visitor without
 * JavaScript sees the prompt text and selects it normally rather than seeing a
 * dead control.
 */
( function () {
	'use strict';

	var RESET_AFTER = 2200;

	/**
	 * Copy text using the async clipboard API, falling back to a selection.
	 *
	 * navigator.clipboard is unavailable in non-secure contexts, which includes
	 * plain http:// on anything that is not localhost — so the fallback is not
	 * theoretical, it is what runs on a staging site served over http.
	 *
	 * @param {string}      text    The text to copy.
	 * @param {HTMLElement} source  Element holding the text, used by the fallback.
	 * @return {Promise<boolean>} Resolves true when the copy succeeded.
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
	 * Fallback: select the element's text and ask the document to copy it.
	 *
	 * Leaves the text selected on failure, so the visitor can press Ctrl+C —
	 * which is what the failure message tells them to do.
	 *
	 * @param {HTMLElement} source Element holding the text.
	 * @return {boolean} True when the copy command reported success.
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
	 * Wire up one copy button.
	 *
	 * @param {HTMLElement} button The button element.
	 */
	function initButton( button ) {
		var targetId = button.getAttribute( 'data-copy-target' );
		var target = targetId ? document.getElementById( targetId ) : null;

		if ( ! target ) {
			// Nothing to copy: remove the control rather than leave it dead.
			button.remove();
			return;
		}

		var labelEl = button.querySelector( '.js-copy-button-label' );
		var status = button.closest( '.prompt-block' );
		status = status ? status.querySelector( '.prompt-block__status' ) : null;

		var labelDefault = button.getAttribute( 'data-label-default' ) || 'Copy prompt';
		var labelCopied = button.getAttribute( 'data-label-copied' ) || 'Copied';
		var labelFailed = button.getAttribute( 'data-label-failed' ) || 'Press Ctrl+C to copy';

		var resetTimer = null;

		button.addEventListener( 'click', function () {
			// `innerText` rather than `textContent`: the placeholder tokens are
			// wrapped in spans, and innerText gives the rendered text with line
			// breaks preserved, which is what the visitor sees and expects.
			var text = target.innerText || target.textContent || '';

			copyText( text, target ).then( function ( ok ) {
				if ( labelEl ) {
					labelEl.textContent = ok ? labelCopied : labelFailed;
				}

				button.setAttribute( 'data-state', ok ? 'copied' : 'failed' );

				/*
				 * The visible label changes, but a screen reader will not
				 * necessarily announce that. The live region states the outcome
				 * explicitly.
				 */
				if ( status ) {
					status.textContent = ok ? labelCopied : labelFailed;
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

	var buttons = document.querySelectorAll( '.js-copy-button' );

	for ( var i = 0; i < buttons.length; i++ ) {
		initButton( buttons[ i ] );
	}
} )();
