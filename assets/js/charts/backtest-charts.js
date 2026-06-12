/**
 * NeuronAlgo Backtest Charts Bundle
 * VIZ-B — Loader + Validator + Transformer (render-agnostic)
 * VIZ-C — Renderer + State Manager (ApexCharts integration)
 *
 * Dependencies: na-apexcharts (registered in conditional-assets.php)
 */

(function() {
    'use strict';

    // Namespace initialization
    window.NeuronAlgo = window.NeuronAlgo || {};

    // =================================================================
    // VALIDATOR MODULE (VIZ-B)
    // =================================================================
    window.NeuronAlgo.Validator = (function() {
        /**
         * Validates chart data payload.
         * 
         * Requirements:
         * - Must have 'series' as a non-empty array
         * - Each point must have numeric 't' (timestamp)
         * - For equity: each point must have numeric 'v' (value)
         * - For drawdown: each point must have numeric 'dd' (drawdown fraction)
         * - Rejects null, missing, empty, short arrays, or NaN values
         *
         * @param {Object} payload - The parsed JSON payload
         * @param {string} type - 'equity' or 'drawdown' to determine required value key
         * @returns {boolean} - True if valid, false otherwise
         */
        function validate(payload, type) {
            // Reject null/undefined
            if (payload === null || payload === undefined) {
                return false;
            }

            // Must be an object
            if (typeof payload !== 'object' || Array.isArray(payload)) {
                return false;
            }

            // series must exist and be an array
            if (!payload.hasOwnProperty('series')) {
                return false;
            }

            var series = payload.series;
            if (!Array.isArray(series)) {
                return false;
            }



            // Determine the value key based on type
            var valueKey = (type === 'drawdown') ? 'dd' : 'v';

            // Validate each point
            for (var i = 0; i < series.length; i++) {
                var point = series[i];

                // Point must be an object
                if (typeof point !== 'object' || point === null) {
                    return false;
                }

                // Each point must have numeric 't'
                if (!point.hasOwnProperty('t')) {
                    return false;
                }

                // t must be numeric (Number type, not NaN)
                if (typeof point.t !== 'number' || isNaN(point.t)) {
                    return false;
                }

                // Each point must have the value key ('v' or 'dd')
                if (!point.hasOwnProperty(valueKey)) {
                    return false;
                }

                // Value must be numeric (Number type, not NaN)
                if (typeof point[valueKey] !== 'number' || isNaN(point[valueKey])) {
                    return false;
                }
            }

            return true;
        }

        // Public API
        return {
            validate: validate
        };
    })();

    // =================================================================
    // TRANSFORMER MODULE (VIZ-B)
    // =================================================================
    window.NeuronAlgo.Transformer = (function() {
        /**
         * Transforms equity/drawdown data to ApexCharts series format.
         * 
         * Transformation rules:
         * - x = t * 1000 (epoch seconds -> milliseconds)
         * - y = v for equity
         * - y = dd * 100 for drawdown (fraction -> percent)
         * 
         * @param {Object} payload - Validated payload with series array
         * @param {string} type - 'equity' or 'drawdown'
         * @returns {Array} ApexCharts pairs [{x, y}, ...]
         */
        function transform(payload, type) {
            if (!payload || !payload.series || !Array.isArray(payload.series)) {
                return [];
            }

            var series = payload.series;
            var result = [];
            var valueKey = (type === 'drawdown') ? 'dd' : 'v';

            for (var i = 0; i < series.length; i++) {
                var point = series[i];
                
                var t = point.t;
                var rawValue = point[valueKey];
                
                // Skip if we couldn't extract values
                if (t === undefined || rawValue === undefined) {
                    continue;
                }
                
                // Convert t to milliseconds (epoch seconds -> ms)
                var x = t * 1000;
                
                // Convert raw value to y
                var y = (type === 'drawdown') ? rawValue * 100 : rawValue;
                
                result.push({
                    x: x,
                    y: y
                });
            }

            return result;
        }

        // Public API
        return {
            transform: transform
        };
    })();

    // =================================================================
    // EQUITY CHART CONFIGURATION MODULE (VIZ-C)
    // =================================================================
    window.NeuronAlgo.EquityChartConfig = (function() {
        function getOptions(seriesData) {
            return {
                series: [{
                    name: 'Equity Curve',
                    data: seriesData
                }],
                chart: {
                    type: 'area',
                    height: 'auto',
                    fontFamily: 'inherit',
                    foreColor: '#fefefe', // Using a light color for text on dark background
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: true,
                        type: 'x',
                        autoScaleYaxis: true
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.9,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeFormatter: {
                            year: 'yyyy',
                            month: 'MMM ',
                            day: 'dd MMM',
                            hour: 'HH:mm'
                        },
                        style: {
                            colors: '#999'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
                        },
                        style: {
                            colors: '#999'
                        }
                    },
                    opposite: false
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy HH:mm'
                    },
                    y: {
                        formatter: function(value) {
                            return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
                        }
                    },
                    theme: 'dark' // Ensure dark theme tooltip for consistency
                },
                grid: {
                    borderColor: '#444',
                    strokeDashArray: 4
                },
                colors: ['#007bff'] // cool-blue accent
            };
        }

        return {
            getOptions: getOptions
        };
    })();

    // =================================================================
    // DRAWDOWN CHART CONFIGURATION MODULE (VIZ-C)
    // =================================================================
    window.NeuronAlgo.DrawdownChartConfig = (function() {
        function getOptions(seriesData) {
            return {
                series: [{
                    name: 'Drawdown',
                    data: seriesData
                }],
                chart: {
                    type: 'area',
                    height: 'auto',
                    fontFamily: 'inherit',
                    foreColor: '#fefefe',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: true,
                        type: 'x',
                        autoScaleYaxis: true
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                    colors: ['#dc3545'] // Red for drawdown
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.9,
                        stops: [0, 100]
                    },
                    colors: ['#dc3545'] // Red fill
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeFormatter: {
                            year: 'yyyy',
                            month: 'MMM ',
                            day: 'dd MMM',
                            hour: 'HH:mm'
                        },
                        style: {
                            colors: '#999'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return value.toFixed(2) + '%';
                        },
                        style: {
                            colors: '#999'
                        }
                    },
                    opposite: false
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy HH:mm'
                    },
                    y: {
                        formatter: function(value) {
                            return value.toFixed(2) + '%';
                        }
                    },
                    theme: 'dark'
                },
                grid: {
                    borderColor: '#444',
                    strokeDashArray: 4
                },
                colors: ['#dc3545'] // Red for drawdown
            };
        }

        return {
            getOptions: getOptions
        };
    })();

    // =================================================================
    // STATE MANAGER MODULE (VIZ-C)
    // =================================================================
    window.NeuronAlgo.StateManager = (function() {
        var STATES = {
            LOADING: 'loading',
            EMPTY: 'empty',
            ERROR: 'error',
            RENDERED: 'rendered'
        };

        /**
         * Updates the state of a chart container.
         * @param {string} containerId - The ID of the chart container element.
         * @param {string} state - The desired state (loading, empty, error, rendered).
         * @param {string} [message] - Optional message for empty/error states.
         */
        function updateState(containerId, state, message) {
            var container = document.getElementById(containerId);
            if (!container) {
                console.error('Chart container not found:', containerId);
                return;
            }

            // Remove all state classes first to ensure clean transition
            for (var key in STATES) {
                if (STATES.hasOwnProperty(key)) {
                    container.classList.remove('na-chart-state-' + STATES[key]);
                }
            }

            // Add new state class
            container.classList.add('na-chart-state-' + state);

            // Update content based on state
            var contentArea = container.querySelector('.na-chart-content-area');
            if (contentArea) {
                switch (state) {
                    case STATES.LOADING:
                        contentArea.innerHTML = '<div class="na-chart-loading-skeleton"></div>';
                        break;
                    case STATES.EMPTY:
                        contentArea.innerHTML = '<div class="na-chart-fallback-message">' + (message || 'No data available.') + '</div>';
                        break;
                    case STATES.ERROR:
                        contentArea.innerHTML = '<div class="na-chart-fallback-message na-chart-error-message">' + (message || 'An error occurred while loading the chart.') + '</div>';
                        break;
                    case STATES.RENDERED:
                        // Chart is rendered by ApexCharts directly into the canvas
                        contentArea.innerHTML = ''; 
                        break;
                }
            }
        }

        return {
            STATES: STATES,
            updateState: updateState
        };
    })();

    // =================================================================
    // CHART LOADER MODULE (VIZ-C)
    // =================================================================
    window.NeuronAlgo.ChartLoader = (function(Validator, Transformer, StateManager, EquityChartConfig, DrawdownChartConfig) {
        /**
         * Renders a chart into its container.
         * @param {string} chartId - The ID of the chart canvas (e.g., 'na-chart-ff8f3a7-equity').
         * @param {Array} seriesData - Data in ApexCharts format.
         * @param {string} type - 'equity' or 'drawdown'.
         */
        function renderChart(chartId, seriesData, type) {
            var chartElement = document.getElementById(chartId);
            if (!chartElement) {
                console.error('Chart element not found:', chartId);
                return;
            }

            var options;
            if (type === 'equity') {
                options = EquityChartConfig.getOptions(seriesData);
            } else if (type === 'drawdown') {
                options = DrawdownChartConfig.getOptions(seriesData);
            } else {
                console.error('Unknown chart type:', type);
                return;
            }

            // Clear existing chart if any
            if (chartElement.apexcharts) {
                chartElement.apexcharts.destroy();
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();
            StateManager.updateState(chartId, StateManager.STATES.RENDERED);
        }

        /**
         * Processes and renders a single chart instance.
         * @param {string} instanceId - Widget instance ID (e.g., 'na-chart-ff8f3a7').
         */
        function processInstance(instanceId) {
            var equityContainerId = instanceId + '-equity';
            var drawdownContainerId = instanceId + '-drawdown';

            StateManager.updateState(equityContainerId, StateManager.STATES.LOADING);
            StateManager.updateState(drawdownContainerId, StateManager.STATES.LOADING);

            var equityData = { data: [], valid: false, error: false };
            var drawdownData = { data: [], valid: false, error: false };

            // Process Equity Data
            var equityScript = document.getElementById(instanceId + '-equity-data');
            if (equityScript) {
                try {
                    var equityPayload = JSON.parse(equityScript.textContent || equityScript.innerText);
                    if (Validator.validate(equityPayload, 'equity')) {
                        equityData.data = Transformer.transform(equityPayload, 'equity');
                        equityData.valid = equityData.data.length > 0;
                    } else {
                        equityData.error = true; // Mark as error if validation fails
                    }
                } catch (e) {
                    console.error('Error parsing or processing equity data for ' + instanceId + ':', e);
                    equityData.error = true;
                }
            } else {
                 // No script tag found, treat as error for now, as data is required
                 equityData.error = true; 
            }

            // Process Drawdown Data
            var drawdownScript = document.getElementById(instanceId + '-drawdown-data');
            if (drawdownScript) {
                try {
                    var drawdownPayload = JSON.parse(drawdownScript.textContent || drawdownScript.innerText);
                    if (Validator.validate(drawdownPayload, 'drawdown')) {
                        drawdownData.data = Transformer.transform(drawdownPayload, 'drawdown');
                        drawdownData.valid = drawdownData.data.length > 0;
                    } else {
                        drawdownData.error = true; // Mark as error if validation fails
                    }
                } catch (e) {
                    console.error('Error parsing or processing drawdown data for ' + instanceId + ':', e);
                    drawdownData.error = true;
                }
            } else {
                // No script tag found, treat as error for now, as data is required
                drawdownData.error = true;
            }

            // Render or show fallback for Equity Chart
            if (equityData.valid) {
                renderChart(equityContainerId, equityData.data, 'equity');
            } else if (equityData.error) {
                StateManager.updateState(equityContainerId, StateManager.STATES.ERROR, 'Failed to load equity data.');
            } else {
                StateManager.updateState(equityContainerId, StateManager.STATES.EMPTY, 'No equity data to display.');
            }

            // Render or show fallback for Drawdown Chart
            if (drawdownData.valid) {
                renderChart(drawdownContainerId, drawdownData.data, 'drawdown');
            } else if (drawdownData.error) {
                StateManager.updateState(drawdownContainerId, StateManager.STATES.ERROR, 'Failed to load drawdown data.');
            } else {
                StateManager.updateState(drawdownContainerId, StateManager.STATES.EMPTY, 'No drawdown data to display.');
            }
        }

        /**
         * Discovers all chart instances on the page and processes them.
         * Implements IntersectionObserver for lazy loading.
         */
        function loadAllInstances() {
            var chartContainers = document.querySelectorAll('[id^="na-chart-"][id$="-equity"]');
            var observerOptions = {
                root: null, // viewport
                rootMargin: '0px',
                threshold: 0.1 // 10% of the target element is visible
            };

            var observer = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var instanceId = entry.target.id.replace('-equity', '');
                        processInstance(instanceId);
                        observer.unobserve(entry.target); // Stop observing once loaded
                    }
                });
            }, observerOptions);

            chartContainers.forEach(function(container) {
                // Initial state to loading skeleton
                StateManager.updateState(container.id, StateManager.STATES.LOADING);
                var drawdownContainerId = container.id.replace('-equity', '-drawdown');
                StateManager.updateState(drawdownContainerId, StateManager.STATES.LOADING);

                observer.observe(container); // Observe the equity chart container
            });
        }

        // Public API
        return {
            loadAllInstances: loadAllInstances
        };
    })(window.NeuronAlgo.Validator, window.NeuronAlgo.Transformer, window.NeuronAlgo.StateManager, window.NeuronAlgo.EquityChartConfig, window.NeuronAlgo.DrawdownChartConfig);

    // =================================================================
    // INITIALIZATION - Trigger loading on DOMContentLoaded
    // =================================================================
    document.addEventListener('DOMContentLoaded', function() {
        window.NeuronAlgo.ChartLoader.loadAllInstances();
    });

})();