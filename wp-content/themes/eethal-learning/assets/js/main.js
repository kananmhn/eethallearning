/**
 * Eethal Learning — front-end behaviour.
 *
 * Every block guards its own selectors so the file is safe to load on inner
 * pages (blog, archives, 404) where the landing-page sections do not exist.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---- FOOTER YEAR ---- */
	var yearEl = document.getElementById( 'year' );
	if ( yearEl ) {
		yearEl.textContent = new Date().getFullYear();
	}

	/* ---- PARTICLE BACKGROUND ---- */
	var canvas = document.getElementById( 'particles-canvas' );
	if ( canvas && canvas.getContext && ! reduceMotion ) {
		var ctx = canvas.getContext( '2d' );
		var W;
		var H;
		var dots = [];

		var resize = function () {
			W = canvas.width = window.innerWidth;
			H = canvas.height = window.innerHeight;
		};
		resize();
		window.addEventListener( 'resize', resize );

		for ( var i = 0; i < 80; i++ ) {
			dots.push( {
				x: Math.random() * W,
				y: Math.random() * H,
				r: Math.random() * 1.2 + 0.3,
				vx: ( Math.random() - 0.5 ) * 0.25,
				vy: ( Math.random() - 0.5 ) * 0.25,
				o: Math.random() * 0.4 + 0.1
			} );
		}

		var drawDots = function () {
			ctx.clearRect( 0, 0, W, H );

			dots.forEach( function ( d ) {
				d.x += d.vx;
				d.y += d.vy;
				if ( d.x < 0 ) { d.x = W; }
				if ( d.x > W ) { d.x = 0; }
				if ( d.y < 0 ) { d.y = H; }
				if ( d.y > H ) { d.y = 0; }
				ctx.beginPath();
				ctx.arc( d.x, d.y, d.r, 0, Math.PI * 2 );
				ctx.fillStyle = 'rgba(255,59,59,' + ( d.o * 0.5 ) + ')';
				ctx.fill();
			} );

			dots.forEach( function ( a, ai ) {
				dots.forEach( function ( b, bi ) {
					if ( ai >= bi ) {
						return;
					}
					var dx = a.x - b.x;
					var dy = a.y - b.y;
					var dist = Math.sqrt( dx * dx + dy * dy );
					if ( dist < 120 ) {
						ctx.beginPath();
						ctx.moveTo( a.x, a.y );
						ctx.lineTo( b.x, b.y );
						ctx.strokeStyle = 'rgba(255,59,59,' + ( 0.04 * ( 1 - dist / 120 ) ) + ')';
						ctx.lineWidth = 0.5;
						ctx.stroke();
					}
				} );
			} );

			window.requestAnimationFrame( drawDots );
		};

		drawDots();
	}

	/* ---- REVEAL ON SCROLL ---- */
	var revealEls = document.querySelectorAll( '.reveal' );
	if ( revealEls.length ) {
		if ( 'IntersectionObserver' in window ) {
			var revealObs = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'visible' );
						revealObs.unobserve( entry.target );
					}
				} );
			}, { threshold: 0.12 } );

			Array.prototype.forEach.call( revealEls, function ( el ) {
				revealObs.observe( el );
			} );
		} else {
			// No observer support — show everything rather than hiding content.
			Array.prototype.forEach.call( revealEls, function ( el ) {
				el.classList.add( 'visible' );
			} );
		}
	}

	/* ---- PROGRESS BARS ---- */
	window.setTimeout( function () {
		document.querySelectorAll( '.prog-fill' ).forEach( function ( bar ) {
			bar.style.width = bar.dataset.w + '%';
		} );
	}, 600 );

	/* ---- COUNTER ANIMATION ---- */
	var counters = document.querySelectorAll( '.counter[data-target], .big-num[data-target]' );
	if ( counters.length ) {
		var animateCounter = function ( el, target, suffix ) {
			if ( reduceMotion ) {
				el.textContent = target + suffix;
				return;
			}
			var duration = 1800;
			var startTime = null;

			var step = function ( ts ) {
				if ( ! startTime ) {
					startTime = ts;
				}
				var p = Math.min( ( ts - startTime ) / duration, 1 );
				var easeOut = 1 - Math.pow( 1 - p, 4 );
				el.textContent = Math.floor( easeOut * target ) + suffix;
				if ( p < 1 ) {
					window.requestAnimationFrame( step );
				}
			};

			window.requestAnimationFrame( step );
		};

		if ( 'IntersectionObserver' in window ) {
			var counterObs = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}
					animateCounter(
						entry.target,
						parseInt( entry.target.dataset.target, 10 ) || 0,
						entry.target.dataset.suffix || ''
					);
					counterObs.unobserve( entry.target );
				} );
			}, { threshold: 0.5 } );

			Array.prototype.forEach.call( counters, function ( el ) {
				counterObs.observe( el );
			} );
		} else {
			Array.prototype.forEach.call( counters, function ( el ) {
				el.textContent = ( el.dataset.target || '' ) + ( el.dataset.suffix || '' );
			} );
		}
	}

	/* ---- FAQ ACCORDION ---- */
	var toggleFaq = function ( question ) {
		var item = question.parentElement;
		var isOpen = item.classList.contains( 'open' );

		document.querySelectorAll( '.faq-item' ).forEach( function ( other ) {
			other.classList.remove( 'open' );
			var q = other.querySelector( '.faq-q' );
			if ( q ) {
				q.setAttribute( 'aria-expanded', 'false' );
			}
		} );

		if ( ! isOpen ) {
			item.classList.add( 'open' );
			question.setAttribute( 'aria-expanded', 'true' );
		}
	};

	document.querySelectorAll( '.faq-q' ).forEach( function ( question ) {
		question.addEventListener( 'click', function () {
			toggleFaq( question );
		} );
		question.addEventListener( 'keydown', function ( e ) {
			if ( 'Enter' === e.key || ' ' === e.key ) {
				e.preventDefault();
				toggleFaq( question );
			}
		} );
	} );

	/* ---- 3D TILT ---- */
	document.querySelectorAll( '.tilt' ).forEach( function ( card ) {
		card.addEventListener( 'mousemove', function ( e ) {
			var r = card.getBoundingClientRect();
			var x = ( e.clientX - r.left ) / r.width - 0.5;
			var y = ( e.clientY - r.top ) / r.height - 0.5;
			card.style.transform = 'perspective(600px) rotateX(' + ( -y * 10 ) + 'deg) rotateY(' + ( x * 10 ) + 'deg) translateY(-8px)';
		} );
		card.addEventListener( 'mouseleave', function () {
			card.style.transform = '';
		} );
	} );

	/* ---- HEADER SCROLL STATE ---- */
	var header = document.getElementById( 'masthead' );
	if ( header ) {
		var onScroll = function () {
			header.classList.toggle( 'scrolled', window.scrollY > 20 );
		};
		onScroll();
		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	/* ---- MOBILE NAV ---- */
	var navToggle = document.getElementById( 'nav-toggle' );
	var siteNav = document.getElementById( 'site-navigation' );
	if ( navToggle && siteNav ) {
		navToggle.addEventListener( 'click', function () {
			var open = siteNav.classList.toggle( 'is-open' );
			navToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		siteNav.addEventListener( 'click', function ( e ) {
			if ( 'A' === e.target.tagName ) {
				siteNav.classList.remove( 'is-open' );
				navToggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	/* ---- MODAL ---- */
	var modal = document.getElementById( 'commonModal' );
	if ( modal ) {
		var btnClose = document.getElementById( 'closeModal' );
		var mAvatar = document.getElementById( 'm-avatar' );
		var mName = document.getElementById( 'm-name' );
		var mSub = document.getElementById( 'm-sub' );
		var mText = document.getElementById( 'm-text' );
		var mTags = document.getElementById( 'm-tags' );
		var lastFocused = null;

		var openModal = function ( avatarText, avatarClass, name, sub, textNodes, tagsHTML ) {
			lastFocused = document.activeElement;

			mAvatar.textContent = avatarText;
			mAvatar.className = 'modal-avatar ' + avatarClass;
			mName.textContent = name;
			mSub.textContent = sub;

			mText.innerHTML = '';
			textNodes.forEach( function ( node ) {
				mText.appendChild( node );
			} );

			mTags.innerHTML = tagsHTML || '';
			modal.classList.add( 'active' );
			modal.setAttribute( 'aria-hidden', 'false' );
			if ( btnClose ) {
				btnClose.focus();
			}
		};

		var closeModal = function () {
			modal.classList.remove( 'active' );
			modal.setAttribute( 'aria-hidden', 'true' );
			if ( lastFocused ) {
				lastFocused.focus();
			}
		};

		if ( btnClose ) {
			btnClose.addEventListener( 'click', closeModal );
		}

		modal.addEventListener( 'click', function ( e ) {
			if ( e.target === modal ) {
				closeModal();
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && modal.classList.contains( 'active' ) ) {
				closeModal();
			}
		} );

		var makeClickable = function ( card, handler ) {
			card.style.cursor = 'pointer';
			card.setAttribute( 'tabindex', '0' );
			card.setAttribute( 'role', 'button' );
			card.addEventListener( 'click', handler );
			card.addEventListener( 'keydown', function ( e ) {
				if ( 'Enter' === e.key || ' ' === e.key ) {
					e.preventDefault();
					handler();
				}
			} );
		};

		document.querySelectorAll( '.inst-card' ).forEach( function ( card ) {
			makeClickable( card, function () {
				var avatar = card.querySelector( '.inst-avatar' );
				var tags = card.querySelector( '.inst-tags' );
				var bio = document.createElement( 'p' );
				bio.textContent = card.querySelector( '.inst-exp' ).textContent;

				openModal(
					avatar ? avatar.textContent : '',
					avatar ? avatar.className.replace( 'inst-avatar', '' ).trim() : '',
					card.querySelector( '.inst-name' ).textContent,
					card.querySelector( '.inst-role' ).textContent,
					[ bio ],
					tags ? tags.innerHTML : ''
				);
			} );
		} );

		document.querySelectorAll( '.test-card' ).forEach( function ( card ) {
			makeClickable( card, function () {
				var avatar = card.querySelector( '.author-avatar' );
				var stars = card.querySelector( '.stars' );
				var nodes = [];

				if ( stars ) {
					nodes.push( stars.cloneNode( true ) );
				}

				var quote = document.createElement( 'p' );
				quote.textContent = card.querySelector( '.test-text' ).textContent;
				nodes.push( quote );

				openModal(
					avatar ? avatar.textContent : '',
					avatar ? avatar.className.replace( 'author-avatar', '' ).trim() : '',
					card.querySelector( '.author-info h5' ).textContent,
					card.querySelector( '.author-info span' ).textContent,
					nodes,
					''
				);
			} );
		} );
	}
}());
