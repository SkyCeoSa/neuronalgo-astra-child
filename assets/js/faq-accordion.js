/**
 * NeuronAlgo FAQ Accordion
 * Accessible accordion component for FAQ blocks
 */

(function() {
	'use strict';

	// Initialize FAQ accordions on DOM ready
	document.addEventListener('DOMContentLoaded', function() {
		const faqContainers = document.querySelectorAll('.na-faq');
		
		faqContainers.forEach(function(container) {
			initFaqAccordion(container);
		});
	});

	/**
	 * Initialize FAQ accordion functionality
	 * @param {HTMLElement} container - The FAQ container element
	 */
	function initFaqAccordion(container) {
		const questions = container.querySelectorAll('.na-faq-question');
		
		questions.forEach(function(question) {
			const answer = question.nextElementSibling;
			const itemId = question.getAttribute('aria-controls');
			
			// Set up ARIA attributes
			if (itemId && answer) {
				question.setAttribute('aria-expanded', 'false');
				answer.setAttribute('id', itemId);
				answer.setAttribute('role', 'region');
				answer.setAttribute('aria-labelledby', question.id || 'faq-question-' + Math.random().toString(36).substr(2, 9));
				
				// Initially hide the answer
				answer.style.maxHeight = '0';
				answer.style.overflow = 'hidden';
				
				// Add click handler
				question.addEventListener('click', function() {
					toggleFaqItem(question, answer);
				});
				
				// Add keyboard handler for accessibility
				question.addEventListener('keydown', function(e) {
					handleKeydown(e, question, answer);
				});
			}
		});
	}

	/**
	 * Toggle a single FAQ item
	 * @param {HTMLElement} question - The question button
	 * @param {HTMLElement} answer - The answer panel
	 */
	function toggleFaqItem(question, answer) {
		const isExpanded = question.getAttribute('aria-expanded') === 'true';
		
		// Close all items if we want single-open behavior (optional)
		// For now, allowing multiple open items
		
		if (isExpanded) {
			closeFaqItem(question, answer);
		} else {
			openFaqItem(question, answer);
		}
	}

	/**
	 * Open a FAQ item
	 */
	function openFaqItem(question, answer) {
		question.setAttribute('aria-expanded', 'true');
		answer.style.maxHeight = answer.scrollHeight + 'px';
	}

	/**
	 * Close a FAQ item
	 */
	function closeFaqItem(question, answer) {
		question.setAttribute('aria-expanded', 'false');
		answer.style.maxHeight = '0';
	}

	/**
	 * Handle keyboard navigation
	 * @param {KeyboardEvent} e - Keydown event
	 * @param {HTMLElement} question - The question button
	 * @param {HTMLElement} answer - The answer panel
	 */
	function handleKeydown(e, question, answer) {
		// Space or Enter should toggle
		if (e.key === ' ' || e.key === 'Enter') {
			e.preventDefault();
			toggleFaqItem(question, answer);
		}
		
		// Arrow keys for navigation
		const allQuestions = Array.from(question.closest('.na-faq').querySelectorAll('.na-faq-question'));
		const currentIndex = allQuestions.indexOf(question);
		
		if (e.key === 'ArrowDown' && currentIndex < allQuestions.length - 1) {
			e.preventDefault();
			allQuestions[currentIndex + 1].focus();
		} else if (e.key === 'ArrowUp' && currentIndex > 0) {
			e.preventDefault();
			allQuestions[currentIndex - 1].focus();
		}
	}

	// Public API for manual initialization (useful for dynamic content)
	window.NeuronAlgoFAQ = {
		init: initFaqAccordion,
		toggle: toggleFaqItem
	};

})();