( function () {
	const focusableSelector = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
	const reduceMotionQuery = window.matchMedia ? window.matchMedia( '(prefers-reduced-motion: reduce)' ) : null;
	let activeDrawer = null;
	let activeTrigger = null;
	let parallaxHeroes = [];
	let parallaxTicking = false;
	let serviceGuideStates = [];
	let serviceGuideTicking = false;
	let serviceSpotlightStates = [];

	function clamp( value, min, max ) {
		return Math.min( Math.max( value, min ), max );
	}

	function isParallaxEnabled() {
		if ( reduceMotionQuery && reduceMotionQuery.matches ) {
			return false;
		}

		return window.innerWidth > 960;
	}

	function updateHeroParallax() {
		parallaxTicking = false;

		if ( ! parallaxHeroes.length ) {
			return;
		}

		if ( ! isParallaxEnabled() ) {
			parallaxHeroes.forEach( function ( hero ) {
				hero.classList.remove( 'has-parallax' );
				hero.style.setProperty( '--lithia-service-hero-shift', '0px' );
			} );
			return;
		}

		const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;

		parallaxHeroes.forEach( function ( hero ) {
			const rect = hero.getBoundingClientRect();
			const travel = clamp( rect.top * -0.08, -12, 36 );
			const isVisible = rect.bottom > 0 && rect.top < viewportHeight;

			hero.classList.add( 'has-parallax' );

			if ( ! isVisible ) {
				return;
			}

			hero.style.setProperty( '--lithia-service-hero-shift', travel.toFixed( 2 ) + 'px' );
		} );
	}

	function requestHeroParallaxUpdate() {
		if ( parallaxTicking ) {
			return;
		}

		parallaxTicking = true;
		window.requestAnimationFrame( updateHeroParallax );
	}

	function initializeHeroParallax() {
		parallaxHeroes = Array.from( document.querySelectorAll( '.wp-block-lithia-service-page.lithia-service-page-block .lithia-service-hero' ) );

		if ( ! parallaxHeroes.length ) {
			return;
		}

		requestHeroParallaxUpdate();
		window.addEventListener( 'scroll', requestHeroParallaxUpdate, { passive: true } );
		window.addEventListener( 'resize', requestHeroParallaxUpdate );

		if ( reduceMotionQuery ) {
			if ( 'function' === typeof reduceMotionQuery.addEventListener ) {
				reduceMotionQuery.addEventListener( 'change', requestHeroParallaxUpdate );
			} else if ( 'function' === typeof reduceMotionQuery.addListener ) {
				reduceMotionQuery.addListener( requestHeroParallaxUpdate );
			}
		}
	}

	function getServiceGuideActivationOffset() {
		const siteHeader = document.querySelector( '.lithia-site-header' );
		const headerHeight = siteHeader ? siteHeader.getBoundingClientRect().height : 0;

		return Math.max( 108, Math.round( headerHeight + 44 ) );
	}

	function clearServiceGuideState( state ) {
		if ( ! state || ! state.nav ) {
			return;
		}

		state.nav.querySelectorAll( '.is-active' ).forEach( function ( element ) {
			element.classList.remove( 'is-active' );
		} );

		state.nav.querySelectorAll( '.has-active-child' ).forEach( function ( element ) {
			element.classList.remove( 'has-active-child' );
		} );

		state.nav.querySelectorAll( '[aria-current="true"]' ).forEach( function ( element ) {
			element.removeAttribute( 'aria-current' );
		} );
	}

	function setActiveServiceGuideEntry( state, entry ) {
		if ( ! state ) {
			return;
		}

		if ( ! entry ) {
			clearServiceGuideState( state );
			state.activeId = '';
			return;
		}

		if ( state.activeId === entry.id ) {
			return;
		}

		clearServiceGuideState( state );
		state.activeId = entry.id;

		entry.link.classList.add( 'is-active' );
		entry.link.setAttribute( 'aria-current', 'true' );

		if ( entry.isChild ) {
			if ( entry.item ) {
				entry.item.classList.add( 'has-active-child' );
			}

			if ( entry.details ) {
				entry.details.classList.add( 'has-active-child' );
				entry.details.open = true;
			}
		} else if ( entry.item ) {
			entry.item.classList.add( 'is-active' );
		}
	}

	function getActiveServiceGuideEntry( state ) {
		const entries = state ? state.entries : [];

		if ( ! entries || ! entries.length ) {
			return null;
		}

		const activationOffset = getServiceGuideActivationOffset();
		let activeEntry = entries[ 0 ];

		entries.forEach( function ( entry ) {
			const rect = entry.target.getBoundingClientRect();

			if ( rect.top <= activationOffset ) {
				activeEntry = entry;
			}
		} );

		if ( window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 8 ) {
			return entries[ entries.length - 1 ];
		}

		return activeEntry;
	}

	function updateServiceGuides() {
		serviceGuideTicking = false;

		if ( ! serviceGuideStates.length ) {
			return;
		}

		serviceGuideStates.forEach( function ( state ) {
			setActiveServiceGuideEntry( state, getActiveServiceGuideEntry( state ) );
		} );
	}

	function requestServiceGuideUpdate() {
		if ( serviceGuideTicking ) {
			return;
		}

		serviceGuideTicking = true;
		window.requestAnimationFrame( updateServiceGuides );
	}

	function initializeServiceGuides() {
		serviceGuideStates = Array.from(
			document.querySelectorAll( '.wp-block-lithia-service-page.lithia-service-page-block' )
		)
			.map( function ( block ) {
				const nav = block.querySelector( '.lithia-service-sidebar__page-guide-nav' );

				if ( ! nav ) {
					return null;
				}

				const entries = Array.from(
					nav.querySelectorAll( '.lithia-service-sidebar__page-guide-link, .lithia-service-sidebar__page-guide-child-link' )
				)
					.map( function ( link ) {
						const href = link.getAttribute( 'href' ) || '';

						if ( '#' !== href.charAt( 0 ) ) {
							return null;
						}

						const id = href.slice( 1 );
						const target = id ? document.getElementById( id ) : null;

						if ( ! target ) {
							return null;
						}

						return {
							id: id,
							link: link,
							target: target,
							item: link.closest( '.lithia-service-sidebar__page-guide-item' ),
							details: link.closest( '.lithia-service-sidebar__page-guide-details' ),
							isChild: link.classList.contains( 'lithia-service-sidebar__page-guide-child-link' ),
						};
					} )
					.filter( Boolean );

				if ( ! entries.length ) {
					return null;
				}

				return {
					nav: nav,
					entries: entries,
					activeId: '',
				};
			} )
			.filter( Boolean );

		if ( ! serviceGuideStates.length ) {
			return;
		}

		requestServiceGuideUpdate();
		window.addEventListener( 'scroll', requestServiceGuideUpdate, { passive: true } );
		window.addEventListener( 'resize', requestServiceGuideUpdate );
		window.addEventListener( 'hashchange', requestServiceGuideUpdate );
	}

	function setActiveServiceSpotlightSlide( state, index ) {
		if ( ! state || ! state.slides.length ) {
			return;
		}

		const slideCount = state.slides.length;
		const nextIndex = ( index + slideCount ) % slideCount;

		state.activeIndex = nextIndex;

		state.slides.forEach( function ( slide, slideIndex ) {
			const isActive = slideIndex === nextIndex;

			slide.classList.toggle( 'is-active', isActive );
			slide.setAttribute( 'aria-hidden', isActive ? 'false' : 'true' );

			if ( isActive ) {
				slide.removeAttribute( 'hidden' );
			} else {
				slide.setAttribute( 'hidden', 'hidden' );
			}
		} );

		state.dots.forEach( function ( dot, dotIndex ) {
			const isActive = dotIndex === nextIndex;

			dot.classList.toggle( 'is-active', isActive );
			dot.setAttribute( 'aria-current', isActive ? 'true' : 'false' );
		} );
	}

	function initializeServiceSpotlights() {
		serviceSpotlightStates = Array.from(
			document.querySelectorAll( '.wp-block-lithia-service-spotlight-loop.lithia-service-spotlight-loop' )
		)
			.map( function ( block ) {
				const slides = Array.from( block.querySelectorAll( '[data-lithia-service-spotlight-slide]' ) );

				if ( ! slides.length ) {
					return null;
				}

				const state = {
					block: block,
					slides: slides,
					dots: Array.from( block.querySelectorAll( '[data-lithia-service-spotlight-dot]' ) ),
					prev: block.querySelector( '[data-lithia-service-spotlight-prev]' ),
					next: block.querySelector( '[data-lithia-service-spotlight-next]' ),
					activeIndex: 0
				};

				if ( state.prev ) {
					state.prev.addEventListener( 'click', function () {
						setActiveServiceSpotlightSlide( state, state.activeIndex - 1 );
					} );
				}

				if ( state.next ) {
					state.next.addEventListener( 'click', function () {
						setActiveServiceSpotlightSlide( state, state.activeIndex + 1 );
					} );
				}

				state.dots.forEach( function ( dot ) {
					dot.addEventListener( 'click', function () {
						const index = Number.parseInt( dot.getAttribute( 'data-slide-index' ) || '0', 10 );

						setActiveServiceSpotlightSlide( state, Number.isNaN( index ) ? 0 : index );
					} );
				} );

				return state;
			} )
			.filter( Boolean );

		serviceSpotlightStates.forEach( function ( state ) {
			setActiveServiceSpotlightSlide( state, 0 );
		} );
	}

	function getFocusableElements( container ) {
		if ( ! container ) {
			return [];
		}

		return Array.from( container.querySelectorAll( focusableSelector ) ).filter(
			( element ) => ! element.hasAttribute( 'hidden' )
		);
	}

	function lockPageScroll() {
		document.body.classList.add( 'has-lithia-service-drawer-open' );
	}

	function unlockPageScroll() {
		document.body.classList.remove( 'has-lithia-service-drawer-open' );
	}

	function openDrawer( trigger ) {
		const targetId = trigger.getAttribute( 'data-service-booking-target' );
		const drawer = targetId ? document.getElementById( targetId ) : null;

		if ( ! drawer ) {
			return;
		}

		if ( activeDrawer && activeDrawer !== drawer ) {
			closeDrawer( activeDrawer, false );
		}

		activeDrawer = drawer;
		activeTrigger = trigger;

		drawer.hidden = false;
		drawer.setAttribute( 'aria-hidden', 'false' );
		trigger.setAttribute( 'aria-expanded', 'true' );
		lockPageScroll();

		window.requestAnimationFrame( function () {
			drawer.classList.add( 'is-open' );
		} );

		const focusTarget = getFocusableElements( drawer )[ 0 ];

		if ( focusTarget ) {
			focusTarget.focus( { preventScroll: true } );
		}
	}

	function closeDrawer( drawer, restoreFocus = true ) {
		if ( ! drawer ) {
			return;
		}

		drawer.classList.remove( 'is-open' );
		drawer.setAttribute( 'aria-hidden', 'true' );

		document
			.querySelectorAll( '[data-service-booking-open][data-service-booking-target="' + drawer.id + '"]' )
			.forEach( function ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			} );

		window.setTimeout( function () {
			drawer.hidden = true;

			if ( drawer === activeDrawer ) {
				activeDrawer = null;
				unlockPageScroll();

				if ( restoreFocus && activeTrigger ) {
					activeTrigger.focus( { preventScroll: true } );
				}

				activeTrigger = null;
			}
		}, 220 );
	}

	document.addEventListener( 'click', function ( event ) {
		const openTrigger = event.target.closest( '[data-service-booking-open]' );

		if ( openTrigger ) {
			event.preventDefault();
			openDrawer( openTrigger );
			return;
		}

		const closeTrigger = event.target.closest( '[data-service-booking-close]' );

		if ( closeTrigger ) {
			closeDrawer( closeTrigger.closest( '.lithia-service-drawer' ) || activeDrawer );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( ! activeDrawer ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			event.preventDefault();
			closeDrawer( activeDrawer );
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		const focusable = getFocusableElements( activeDrawer );

		if ( ! focusable.length ) {
			return;
		}

		const first = focusable[ 0 ];
		const last = focusable[ focusable.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	initializeHeroParallax();
	initializeServiceGuides();
	initializeServiceSpotlights();
}() );
