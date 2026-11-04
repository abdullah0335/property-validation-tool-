/**
 * PropVal - Frontend Application
 * Handles form submission, API calls, and dynamic rendering of results
 * including AI valuation and multi-portal search links.
 */

(function () {
    'use strict';

    // -- DOM refs ------------------------------------------------------------
    const form           = document.getElementById('valuationForm');
    const searchBtn      = document.getElementById('searchBtn');
    const btnText        = searchBtn.querySelector('.btn-text');
    const btnLoader      = searchBtn.querySelector('.btn-loader');
    const resultsSection = document.getElementById('resultsSection');
    const errorBanner    = document.getElementById('errorBanner');
    const errorMessage   = document.getElementById('errorMessage');

    // Portal prefix mapping
    const PORTALS = [
        { key: 'rightmove',     prefix: 'rm',  label: 'Rightmove',     cls: 'rm'  },
        { key: 'zoopla',        prefix: 'zp',  label: 'Zoopla',        cls: 'zp'  },
        { key: 'onthemarket',   prefix: 'otm', label: 'OnTheMarket',   cls: 'otm' },
        { key: 'primelocation', prefix: 'pl',  label: 'PrimeLocation', cls: 'pl'  },
        { key: 'homecouk',      prefix: 'hc',  label: 'Home.co.uk',    cls: 'hc'  },
    ];

    // -- Helpers -------------------------------------------------------------
    const fmt = (n) => '£' + Number(n).toLocaleString('en-GB');

    function setLoading(on) {
        btnText.style.display   = on ? 'none' : '';
        btnLoader.style.display = on ? 'flex' : 'none';
        searchBtn.disabled      = on;
    }

    function showError(msg) {
        errorMessage.textContent = msg;
        errorBanner.style.display = 'block';
        setTimeout(() => { errorBanner.style.display = 'none'; }, 6000);
    }

    function hideError() { errorBanner.style.display = 'none'; }

    function setPortalLoading(prefix) {
        const priceEl = document.getElementById(prefix + '-price');
        if (priceEl) priceEl.textContent = '-';
        const statusEl = document.getElementById(prefix + '-status');
        if (statusEl) statusEl.innerHTML = '<span class="dot loading"></span> Fetching...';
        ['meta', 'count', 'range', 'sqft'].forEach(function(k) {
            var el = document.getElementById(prefix + '-' + k);
            if (el) el.textContent = '-';
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function titleCase(str) {
        return str.replace(/-/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
    }

    // -- Portal result renderer ----------------------------------------------
    function renderPortal(prefix, data) {
        var priceEl  = document.getElementById(prefix + '-price');
        var statusEl = document.getElementById(prefix + '-status');
        var metaEl   = document.getElementById(prefix + '-meta');
        var countEl  = document.getElementById(prefix + '-count');
        var rangeEl  = document.getElementById(prefix + '-range');
        var sqftEl   = document.getElementById(prefix + '-sqft');
        var linkEl   = document.getElementById(prefix + '-link');

        if (!priceEl) return;

        if (linkEl && data && data.url) {
            linkEl.href = data.url;
        }

        if (!data || data.status !== 'ok' || !data.average) {
            if (statusEl) statusEl.innerHTML = '<span class="dot error"></span> Unavailable';
            priceEl.textContent = 'N/A';
            return;
        }

        priceEl.textContent  = fmt(data.average);
        if (statusEl) statusEl.innerHTML = '<span class="dot ok"></span> ' + data.count + ' listings';
        if (metaEl)   metaEl.textContent = 'Average of ' + data.count + ' properties';
        if (countEl)  countEl.textContent = data.count;
        if (rangeEl)  rangeEl.textContent = data.min && data.max ? fmt(data.min) + ' – ' + fmt(data.max) : '-';
        if (sqftEl)   sqftEl.textContent = data.per_sqft ? fmt(data.per_sqft) + ' / sq ft' : '-';

    }

    // -- AI Valuation renderer -----------------------------------------------
    function renderAiValuation(ai) {
        var section = document.getElementById('aiSection');
        if (!ai || ai.status !== 'ok') {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';

        document.getElementById('aiEstimate').textContent = fmt(ai.estimate);
        document.getElementById('aiConfidence').textContent = ai.confidence + '%';

        var fill = document.getElementById('aiConfidenceFill');
        fill.style.width = '0%';
        fill.className = 'ai-confidence-fill';
        if (ai.confidence >= 75) fill.classList.add('high');
        else if (ai.confidence >= 50) fill.classList.add('moderate');
        else fill.classList.add('low');

        requestAnimationFrame(function() {
            fill.style.width = ai.confidence + '%';
        });

        document.getElementById('aiRange').textContent = fmt(ai.low) + ' – ' + fmt(ai.high);
        document.getElementById('aiWeightedAvg').textContent = fmt(ai.weighted_average);
        document.getElementById('aiMedian').textContent = fmt(ai.median);
        document.getElementById('aiPortalsUsed').textContent = ai.portals_analysed;
        document.getElementById('aiTotalListings').textContent = ai.total_listings;

        var reasoningEl = document.getElementById('aiReasoning');
        reasoningEl.innerHTML = '';
        if (ai.reasoning && ai.reasoning.length) {
            var ul = document.createElement('ul');
            ul.className = 'ai-reasoning-list';
            ai.reasoning.forEach(function(r) {
                var li = document.createElement('li');
                li.textContent = r;
                ul.appendChild(li);
            });
            reasoningEl.appendChild(ul);
        }
    }

    // -- Comparison bars -----------------------------------------------------
    function renderComparison(portals) {
        var container = document.getElementById('comparisonBars');
        container.innerHTML = '';

        var maxVal = 0;
        PORTALS.forEach(function(p) {
            var d = portals[p.key];
            if (d && d.average > maxVal) maxVal = d.average;
        });

        PORTALS.forEach(function(p) {
            var data = portals[p.key];
            if (!data || !data.average) return;

            var pct = ((data.average / maxVal) * 100).toFixed(1);

            var row = document.createElement('div');
            row.className = 'bar-row';
            row.innerHTML =
                '<div class="bar-label">' + p.label + '</div>' +
                '<div class="bar-track">' +
                    '<div class="bar-fill ' + p.cls + '" style="width:0%" data-pct="' + pct + '%"></div>' +
                '</div>' +
                '<div class="bar-value">' + fmt(data.average) + '</div>';
            container.appendChild(row);
        });

        requestAnimationFrame(function() {
            container.querySelectorAll('.bar-fill').forEach(function(el) {
                el.style.width = el.dataset.pct;
            });
        });
    }

    // -- Portal search links -------------------------------------------------
    function renderPortalLinks(portalLinks) {
        var section = document.getElementById('portalLinksSection');
        var grid    = document.getElementById('portalLinksGrid');

        if (!portalLinks || Object.keys(portalLinks).length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';
        grid.innerHTML = '';

        var colorMap = {
            rightmove:     'rm',
            zoopla:        'zp',
            onthemarket:   'otm',
            primelocation: 'pl',
            homecouk:      'hc',
        };

        Object.keys(portalLinks).forEach(function(key) {
            var link = portalLinks[key];
            var cls  = colorMap[key] || '';

            var a = document.createElement('a');
            a.className = 'portal-search-link ' + cls;
            a.href = link.url;
            a.target = '_blank';
            a.rel = 'noopener';
            a.innerHTML =
                '<span class="psl-icon ' + cls + '">' + link.name.substring(0, 2).toUpperCase() + '</span>' +
                '<span class="psl-text">' +
                    '<span class="psl-name">' + escapeHtml(link.name) + '</span>' +
                    '<span class="psl-action">View related properties &rarr;</span>' +
                '</span>';
            grid.appendChild(a);
        });
    }

    // -- Sample listings -----------------------------------------------------
    function renderListings(portals) {
        var grid = document.getElementById('listingsGrid');
        grid.innerHTML = '';

        var portalMeta = {
            rightmove:     { cls: 'rm',  label: 'Rightmove' },
            zoopla:        { cls: 'zp',  label: 'Zoopla'    },
            onthemarket:   { cls: 'otm', label: 'OTM'       },
            primelocation: { cls: 'pl',  label: 'PrimeLoc'  },
            homecouk:      { cls: 'hc',  label: 'Home.co'   },
        };

        var allListings = [];
        Object.keys(portals).forEach(function(key) {
            var data = portals[key];
            var meta = portalMeta[key];
            if (!data || !data.listings || !meta) return;
            data.listings.slice(0, 2).forEach(function(l) {
                allListings.push(Object.assign({}, l, meta));
            });
        });

        allListings.sort(function(a, b) { return a.price - b.price; });

        allListings.slice(0, 8).forEach(function(l) {
            var cls   = l.cls || '';
            var label = l.label || l.portal || 'Portal';
            var card = document.createElement('a');
            card.className = 'listing-card ' + cls;
            card.href = l.search_url || '#';
            card.target = '_blank';
            card.rel = 'noopener';
            card.innerHTML =
                '<div class="listing-price-row">' +
                    '<div class="listing-price">' + fmt(l.price) + '</div>' +
                    '<span class="listing-badge ' + cls + '">' + label + '</span>' +
                '</div>' +
                '<div class="listing-address">' + escapeHtml(l.address) + '</div>' +
                '<div class="listing-desc">' + escapeHtml(l.desc) + '</div>' +
                '<div class="listing-footer">' +
                    '<span class="listing-beds">' +
                        '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                            '<path d="M3 22V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v13M3 11h18M7 11V7M17 11V7"/>' +
                        '</svg>' +
                        l.beds + ' bed · ' + escapeHtml(l.type || 'Property') +
                    '</span>' +
                    '<span class="listing-view">View on portal &rarr;</span>' +
                '</div>';
            grid.appendChild(card);
        });

        if (allListings.length === 0) {
            grid.innerHTML = '<p style="color:var(--muted);font-size:14px;">No sample listings available for this search.</p>';
        }
    }

    // -- Main render ---------------------------------------------------------
    function renderResults(data) {
        // Header
        var locationText = data.location;
        if (data.property_type !== 'any') locationText += ' · ' + titleCase(data.property_type);
        if (data.bedrooms > 0) locationText += ' · ' + data.bedrooms + ' bed';
        document.getElementById('resultsLocation').textContent = locationText;

        var totalListings = 0;
        var portalCount = 0;
        Object.values(data.portals).forEach(function(p) {
            if (p && p.count) { totalListings += p.count; portalCount++; }
        });
        document.getElementById('resultsCount').textContent =
            totalListings + ' properties analysed across ' + portalCount + ' portals';

        // Market badge
        var badge = document.getElementById('marketTrend');
        if (data.vs_national_pct > 0) {
            badge.textContent  = '+' + data.vs_national_pct + '% above national average';
            badge.className    = 'market-badge above';
        } else {
            badge.textContent  = data.vs_national_pct + '% below national average';
            badge.className    = 'market-badge below';
        }

        // Average hero
        document.getElementById('averagePrice').textContent = fmt(data.overall_average);
        document.getElementById('averageSub').textContent =
            'Combined average across ' + portalCount + ' property portals';

        // Portal cards
        PORTALS.forEach(function(p) {
            renderPortal(p.prefix, data.portals[p.key]);
        });

        // AI valuation
        renderAiValuation(data.ai_valuation);

        // Comparison bars & listings
        renderComparison(data.portals);
        renderPortalLinks(data.portal_links);
        renderListings(data.portals);

        // Show section
        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // -- Form submit ---------------------------------------------------------
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError();
        setLoading(true);

        // Reset portal cards to loading state
        PORTALS.forEach(function(p) { setPortalLoading(p.prefix); });
        document.getElementById('aiSection').style.display = 'none';
        document.getElementById('portalLinksSection').style.display = 'none';
        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

        var location      = document.getElementById('location').value.trim();
        var property_type = document.getElementById('property_type').value;
        var bedrooms      = document.getElementById('bedrooms').value;

        var params = new URLSearchParams({ location: location, property_type: property_type, bedrooms: bedrooms });

        fetch('api/valuation.php?' + params.toString())
            .then(function(res) {
                if (!res.ok) throw new Error('Server error: ' + res.status);
                return res.json();
            })
            .then(function(data) {
                if (data.error) throw new Error(data.error);
                renderResults(data);
            })
            .catch(function(err) {
                showError(err.message || 'Failed to fetch valuations. Please try again.');
                resultsSection.style.display = 'none';
            })
            .finally(function() {
                setLoading(false);
            });
    });

})();
