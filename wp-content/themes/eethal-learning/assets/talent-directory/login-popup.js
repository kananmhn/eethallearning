/**
 * Talent Pool sign-in popup for the rest of the site (home page menu etc.).
 *
 * Links to the Talent Pool pages (Dashboard, Working Professionals, Students) open this
 * popup instead of navigating, while the visitor isn't signed in. After a successful
 * sign-in it goes to the page that was clicked. Settings come from window.EETHAL_TD_LOGIN
 * (see eethal_td_enqueue_login_popup()).
 */
(function () {
	var cfg = window.EETHAL_TD_LOGIN;
	if (!cfg) return;

	var clean = function (p) { return p.replace(/\/+$/, ''); };
	var gated = (cfg.pages || []).map(function (u) { return clean(new URL(u, location.href).pathname); });
	var overlay = null;
	var target = '';

	function isGated(a) {
		if (!a || !a.href) return false;
		var url = new URL(a.href, location.href);
		return url.origin === location.origin && gated.indexOf(clean(url.pathname)) !== -1;
	}

	function el(tag, attrs, html) {
		var n = document.createElement(tag);
		for (var k in attrs) n.setAttribute(k, attrs[k]);
		if (html) n.innerHTML = html;
		return n;
	}

	function build() {
		overlay = el('div', { 'class': 'etl-overlay', role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'etl-title' });
		overlay.innerHTML =
			'<form class="etl-card" novalidate>' +
				'<button type="button" class="etl-close" aria-label="Close">&times;</button>' +
				(cfg.logo ? '<img class="etl-logo" src="' + cfg.logo + '" alt="Eethal Learning">' : '') +
				'<h3 id="etl-title">Sign in to Talent Pool</h3>' +
				'<p class="etl-sub">Please sign in to view working professionals and students.</p>' +
				'<label class="etl-field"><span>Email Address</span><input type="text" name="email" autocomplete="username" placeholder="name@example.com" required></label>' +
				'<label class="etl-field"><span>Password</span>' +
					'<span class="etl-pw"><input type="password" name="password" autocomplete="current-password" placeholder="Enter password" required>' +
					'<button type="button" class="etl-eye" aria-label="Show password">Show</button></span></label>' +
				'<div class="etl-error" role="alert" hidden></div>' +
				'<button type="submit" class="etl-submit">Sign In</button>' +
			'</form>';
		document.body.appendChild(overlay);

		var form = overlay.querySelector('form');
		var error = overlay.querySelector('.etl-error');
		var submit = overlay.querySelector('.etl-submit');
		var pw = form.elements.password;

		overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(); });
		overlay.querySelector('.etl-close').addEventListener('click', close);
		overlay.querySelector('.etl-eye').addEventListener('click', function () {
			var show = pw.type === 'password';
			pw.type = show ? 'text' : 'password';
			this.textContent = show ? 'Hide' : 'Show';
			this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
		});
		form.addEventListener('input', function () { error.hidden = true; });

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var email = form.elements.email.value.trim();
			if (!email || !pw.value) { showError('Please enter your email and password.'); return; }
			submit.disabled = true;
			submit.textContent = 'Signing in...';
			fetch(cfg.restUrl + 'auth/login', {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
				// The account's role decides what they can do: Talent Pool Viewer or Talent Directory Admin.
				body: JSON.stringify({ email: email, password: pw.value })
			}).then(function (res) {
				return res.json().catch(function () { return {}; }).then(function (data) {
					if (!res.ok) throw new Error(data.message || 'Incorrect email or password.');
					submit.textContent = 'Opening Talent Pool...';
					location.href = target || cfg.pages[0];
				});
			}).catch(function (err) {
				showError(err && err.message && err.message !== 'Failed to fetch' ? err.message : "Couldn't reach the server. Please try again.");
				submit.disabled = false;
				submit.textContent = 'Sign In';
			});
		});

		function showError(msg) { error.textContent = msg; error.hidden = false; }
	}

	function open(href) {
		target = href;
		if (!overlay) build();
		overlay.classList.add('is-open');
		document.documentElement.classList.add('etl-lock');
		setTimeout(function () { overlay.querySelector('input[name=email]').focus(); }, 50);
	}

	function close() {
		if (!overlay) return;
		overlay.classList.remove('is-open');
		document.documentElement.classList.remove('etl-lock');
	}

	document.addEventListener('click', function (e) {
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
		var a = e.target.closest ? e.target.closest('a') : null;
		if (!isGated(a)) return;
		e.preventDefault();
		open(a.href);
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) close();
	});
})();
