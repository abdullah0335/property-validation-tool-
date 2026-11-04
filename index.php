<?php
// index.php - Main entry point
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropVal - UK Property Valuation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=3">
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="logo">
            <span class="logo-mark">P</span>
            <span class="logo-text">PropVal</span>
        </div>
        <p class="tagline">AI-powered UK property valuations - aggregated from 5 major portals</p>
    </div>
</header>

<main>
    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-card">
                <h1 class="search-title">What is your property worth?</h1>
                <p class="search-subtitle">Enter a UK postcode or location to get AI-analysed valuations from Rightmove, Zoopla, OnTheMarket, PrimeLocation and Home.co.uk.</p>

                <form id="valuationForm" class="search-form">
                    <div class="input-row">
                        <div class="input-group">
                            <label for="location">Location / Postcode</label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                placeholder="e.g. SW1A 1AA or Mayfair, London"
                                autocomplete="off"
                                required
                            >
                        </div>
                        <div class="input-group">
                            <label for="property_type">Property Type</label>
                            <select id="property_type" name="property_type">
                                <option value="any">Any Type</option>
                                <option value="detached">Detached</option>
                                <option value="semi-detached">Semi-Detached</option>
                                <option value="terraced">Terraced</option>
                                <option value="flat">Flat / Apartment</option>
                                <option value="bungalow">Bungalow</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="bedrooms">Bedrooms</label>
                            <select id="bedrooms" name="bedrooms">
                                <option value="0">Any</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5+</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-search" id="searchBtn">
                            <span class="btn-text">Get Valuation</span>
                            <span class="btn-loader" style="display:none;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="results-section" id="resultsSection" style="display:none;">
        <div class="container">

            <!-- Summary Bar -->
            <div class="results-header">
                <div class="results-meta">
                    <span class="results-location" id="resultsLocation"></span>
                    <span class="results-count" id="resultsCount"></span>
                </div>
                <div class="market-badge" id="marketTrend"></div>
            </div>

            <!-- Average Price Hero -->
            <div class="average-hero">
                <div class="average-label">Market Average</div>
                <div class="average-price" id="averagePrice">-</div>
                <div class="average-sub" id="averageSub"></div>
            </div>

            <!-- AI Valuation Section -->
            <div class="ai-section" id="aiSection" style="display:none;">
                <div class="ai-header">
                    <div class="ai-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2a4 4 0 0 1 4 4v1a3 3 0 0 1 3 3v1a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2v-1a3 3 0 0 1 3-3V6a4 4 0 0 1 4-4z"/>
                            <circle cx="9" cy="15" r="1"/><circle cx="15" cy="15" r="1"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="ai-title">AI-Powered Valuation</h3>
                        <p class="ai-subtitle">Statistical analysis across all portals</p>
                    </div>
                </div>
                <div class="ai-body">
                    <div class="ai-estimate-row">
                        <div class="ai-estimate-block">
                            <div class="ai-estimate-label">AI Estimate</div>
                            <div class="ai-estimate-price" id="aiEstimate">-</div>
                        </div>
                        <div class="ai-confidence-block">
                            <div class="ai-confidence-label">Confidence</div>
                            <div class="ai-confidence-value" id="aiConfidence">-</div>
                            <div class="ai-confidence-bar">
                                <div class="ai-confidence-fill" id="aiConfidenceFill"></div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-range">
                        <span class="ai-range-label">Valuation Range:</span>
                        <span class="ai-range-value" id="aiRange">-</span>
                    </div>
                    <div class="ai-stats-row">
                        <div class="ai-stat">
                            <span class="ai-stat-label">Weighted Avg</span>
                            <span class="ai-stat-value" id="aiWeightedAvg">-</span>
                        </div>
                        <div class="ai-stat">
                            <span class="ai-stat-label">Median Price</span>
                            <span class="ai-stat-value" id="aiMedian">-</span>
                        </div>
                        <div class="ai-stat">
                            <span class="ai-stat-label">Portals Used</span>
                            <span class="ai-stat-value" id="aiPortalsUsed">-</span>
                        </div>
                        <div class="ai-stat">
                            <span class="ai-stat-label">Total Listings</span>
                            <span class="ai-stat-value" id="aiTotalListings">-</span>
                        </div>
                    </div>
                    <div class="ai-reasoning" id="aiReasoning"></div>
                </div>
            </div>

            <!-- Portal Cards -->
            <div class="portal-grid">

                <div class="portal-card" id="card-rightmove">
                    <div class="portal-header rightmove-header">
                        <div class="portal-logo">
                            <div class="portal-icon rightmove-icon">RM</div>
                            <div>
                                <div class="portal-name">Rightmove</div>
                                <div class="portal-url">rightmove.co.uk</div>
                            </div>
                        </div>
                        <div class="portal-status" id="rm-status">
                            <span class="dot loading"></span> Fetching...
                        </div>
                    </div>
                    <div class="portal-body">
                        <div class="portal-price" id="rm-price">-</div>
                        <div class="portal-meta" id="rm-meta"></div>
                        <div class="portal-listings">
                            <div class="listing-stat">
                                <span class="stat-label">Listings found</span>
                                <span class="stat-value" id="rm-count">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Price range</span>
                                <span class="stat-value" id="rm-range">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Avg. per sq ft</span>
                                <span class="stat-value" id="rm-sqft">-</span>
                            </div>
                        </div>
                        <a class="portal-link" id="rm-link" href="#" target="_blank" rel="noopener">View on Rightmove &rarr;</a>
                    </div>
                </div>

                <div class="portal-card" id="card-zoopla">
                    <div class="portal-header zoopla-header">
                        <div class="portal-logo">
                            <div class="portal-icon zoopla-icon">ZP</div>
                            <div>
                                <div class="portal-name">Zoopla</div>
                                <div class="portal-url">zoopla.co.uk</div>
                            </div>
                        </div>
                        <div class="portal-status" id="zp-status">
                            <span class="dot loading"></span> Fetching...
                        </div>
                    </div>
                    <div class="portal-body">
                        <div class="portal-price" id="zp-price">-</div>
                        <div class="portal-meta" id="zp-meta"></div>
                        <div class="portal-listings">
                            <div class="listing-stat">
                                <span class="stat-label">Listings found</span>
                                <span class="stat-value" id="zp-count">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Price range</span>
                                <span class="stat-value" id="zp-range">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Avg. per sq ft</span>
                                <span class="stat-value" id="zp-sqft">-</span>
                            </div>
                        </div>
                        <a class="portal-link" id="zp-link" href="#" target="_blank" rel="noopener">View on Zoopla &rarr;</a>
                    </div>
                </div>

                <div class="portal-card" id="card-otm">
                    <div class="portal-header otm-header">
                        <div class="portal-logo">
                            <div class="portal-icon otm-icon">OTM</div>
                            <div>
                                <div class="portal-name">OnTheMarket</div>
                                <div class="portal-url">onthemarket.com</div>
                            </div>
                        </div>
                        <div class="portal-status" id="otm-status">
                            <span class="dot loading"></span> Fetching...
                        </div>
                    </div>
                    <div class="portal-body">
                        <div class="portal-price" id="otm-price">-</div>
                        <div class="portal-meta" id="otm-meta"></div>
                        <div class="portal-listings">
                            <div class="listing-stat">
                                <span class="stat-label">Listings found</span>
                                <span class="stat-value" id="otm-count">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Price range</span>
                                <span class="stat-value" id="otm-range">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Avg. per sq ft</span>
                                <span class="stat-value" id="otm-sqft">-</span>
                            </div>
                        </div>
                        <a class="portal-link" id="otm-link" href="#" target="_blank" rel="noopener">View on OnTheMarket &rarr;</a>
                    </div>
                </div>

                <div class="portal-card" id="card-primelocation">
                    <div class="portal-header pl-header">
                        <div class="portal-logo">
                            <div class="portal-icon pl-icon">PL</div>
                            <div>
                                <div class="portal-name">PrimeLocation</div>
                                <div class="portal-url">primelocation.com</div>
                            </div>
                        </div>
                        <div class="portal-status" id="pl-status">
                            <span class="dot loading"></span> Fetching...
                        </div>
                    </div>
                    <div class="portal-body">
                        <div class="portal-price" id="pl-price">-</div>
                        <div class="portal-meta" id="pl-meta"></div>
                        <div class="portal-listings">
                            <div class="listing-stat">
                                <span class="stat-label">Listings found</span>
                                <span class="stat-value" id="pl-count">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Price range</span>
                                <span class="stat-value" id="pl-range">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Avg. per sq ft</span>
                                <span class="stat-value" id="pl-sqft">-</span>
                            </div>
                        </div>
                        <a class="portal-link" id="pl-link" href="#" target="_blank" rel="noopener">View on PrimeLocation &rarr;</a>
                    </div>
                </div>

                <div class="portal-card" id="card-homecouk">
                    <div class="portal-header hc-header">
                        <div class="portal-logo">
                            <div class="portal-icon hc-icon">HC</div>
                            <div>
                                <div class="portal-name">Home.co.uk</div>
                                <div class="portal-url">home.co.uk</div>
                            </div>
                        </div>
                        <div class="portal-status" id="hc-status">
                            <span class="dot loading"></span> Fetching...
                        </div>
                    </div>
                    <div class="portal-body">
                        <div class="portal-price" id="hc-price">-</div>
                        <div class="portal-meta" id="hc-meta"></div>
                        <div class="portal-listings">
                            <div class="listing-stat">
                                <span class="stat-label">Listings found</span>
                                <span class="stat-value" id="hc-count">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Price range</span>
                                <span class="stat-value" id="hc-range">-</span>
                            </div>
                            <div class="listing-stat">
                                <span class="stat-label">Avg. per sq ft</span>
                                <span class="stat-value" id="hc-sqft">-</span>
                            </div>
                        </div>
                        <a class="portal-link" id="hc-link" href="#" target="_blank" rel="noopener">View on Home.co.uk &rarr;</a>
                    </div>
                </div>

            </div>

            <!-- Price Comparison Bar -->
            <div class="comparison-section">
                <h3 class="comparison-title">Price Comparison</h3>
                <div class="comparison-bars" id="comparisonBars"></div>
            </div>

            <!-- Search All Portals -->
            <div class="portal-links-section" id="portalLinksSection" style="display:none;">
                <h3 class="portal-links-title">Search Related Properties on All Portals</h3>
                <p class="portal-links-subtitle">Click any portal below to view live property listings for this search on their website.</p>
                <div class="portal-links-grid" id="portalLinksGrid"></div>
            </div>

            <!-- Sample Listings -->
            <div class="listings-section">
                <h3 class="listings-title">Sample Listings in This Area</h3>
                <div class="listings-grid" id="listingsGrid"></div>
            </div>

            <!-- Disclaimer -->
            <div class="disclaimer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p>Valuations are AI-estimated averages based on aggregated market data across 5 property portals. The AI confidence score reflects cross-portal consensus. For a precise valuation, consult a local RICS-accredited surveyor.</p>
            </div>

        </div>
    </section>

    <!-- Error Section -->
    <div class="error-banner" id="errorBanner" style="display:none;">
        <div class="container">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span id="errorMessage">Something went wrong. Please try again.</span>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <p>PropVal aggregates data from UK property portals. Not affiliated with Rightmove, Zoopla, OnTheMarket, PrimeLocation, or Home.co.uk.</p>
    </div>
</footer>

<script src="assets/js/app.js?v=3"></script>
</body>
</html>
