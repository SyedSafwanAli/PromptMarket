/**
 * PromptAtlas light/dark theme toggle.
 *
 * The only JavaScript the theme ships. Everything on the page works without it:
 * the OS preference is honoured by CSS via prefers-color-scheme, and this script
 * only adds the ability to override that choice manually.
 *
 * The saved preference is applied by a small inline script in the document head
 * (inc/assets.php) so there is no flash of the wrong theme before this file
 * loads. This file handles the button only.
 *
 * Every localStorage access is wrapped: it throws in some private-browsing modes
 * and whenever site data is blocked, and the toggle must keep working for the
 * current page even when nothing can be persisted.
 */
( function () {
	'use strict';

	var STORAGE_KEY = 'promptatlas-theme';
	var root = document.documentElement;
	var toggle = document.querySelector( '.js-theme-toggle' );

	if ( ! toggle ) {
		return;
	}

	var label = toggle.querySelector( '.js-theme-toggle-label' );

	/**
	 * Read the theme currently in effect.
	 *
	 * Falls back to the OS preference when no manual override is set, so the
	 * button's state always reflects what the visitor is actually looking at.
	 *
	 * @return {string} Either 'dark' or 'light'.
	 */
	function currentTheme() {
		var explicit = root.getAttribute( 'data-theme' );

		if ( explicit === 'dark' || explicit === 'light' ) {
			return explicit;
		}

		return window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' ).matches
			? 'dark'
			: 'light';
	}

	/**
	 * Update the button's accessible state and label.
	 *
	 * The label describes the action the button performs, not the current state,
	 * which is what a screen reader user needs to hear before activating it.
	 *
	 * @param {string} theme The theme now in effect.
	 */
	function syncButton( theme ) {
		var isDark = theme === 'dark';

		toggle.setAttribute( 'aria-pressed', isDark ? 'true' : 'false' );

		if ( label ) {
			label.textContent = isDark
				? toggle.getAttribute( 'data-label-light' ) || 'Switch to light theme'
				: toggle.getAttribute( 'data-label-dark' ) || 'Switch to dark theme';
		}
	}

	/**
	 * Apply a theme and try to remember it.
	 *
	 * @param {string} theme Either 'dark' or 'light'.
	 */
	function applyTheme( theme ) {
		root.setAttribute( 'data-theme', theme );

		try {
			window.localStorage.setItem( STORAGE_KEY, theme );
		} catch ( e ) {
			/* Storage unavailable. The choice still applies to this page. */
		}

		syncButton( theme );
	}

	toggle.addEventListener( 'click', function () {
		applyTheme( currentTheme() === 'dark' ? 'light' : 'dark' );
	} );

	/*
	 * Follow the OS preference while the visitor has not chosen manually. Once
	 * they have, their choice wins and this does nothing — overriding an
	 * explicit choice because the OS changed would be hostile.
	 */
	if ( window.matchMedia ) {
		var query = window.matchMedia( '(prefers-color-scheme: dark)' );

		var onSchemeChange = function ( event ) {
			var stored = null;

			try {
				stored = window.localStorage.getItem( STORAGE_KEY );
			} catch ( e ) {
				/* Ignore: treated as no manual preference. */
			}

			if ( stored === 'dark' || stored === 'light' ) {
				return;
			}

			syncButton( event.matches ? 'dark' : 'light' );
		};

		if ( typeof query.addEventListener === 'function' ) {
			query.addEventListener( 'change', onSchemeChange );
		}
	}

	syncButton( currentTheme() );
} )();
