/**
 * Nexura AI Search Optimizer - Admin JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		// 1. Copy URL to Clipboard
		$(document).on('click', '.naso-copy-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var url = $btn.data('clipboard');

			if (!url) {
				return;
			}

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(function() {
					var originalText = $btn.text();
					$btn.text(nasoAdminData.copiedMsg || 'Copied!').prop('disabled', true);
					setTimeout(function() {
						$btn.text(originalText).prop('disabled', false);
					}, 2000);
				});
			} else {
				// Fallback for older browsers
				var $temp = $('<input>');
				$('body').append($temp);
				$temp.val(url).select();
				document.execCommand('copy');
				$temp.remove();

				var origText = $btn.text();
				$btn.text(nasoAdminData.copiedMsg || 'Copied!').prop('disabled', true);
				setTimeout(function() {
					$btn.text(origText).prop('disabled', false);
				}, 2000);
			}
		});

		// 2. Rebuild AI Cache AJAX
		$('#naso-rebuild-cache-btn, #naso-flush-cache-btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var origHtml = $btn.html();

			$btn.prop('disabled', true).text('Rebuilding...');

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_rebuild_cache',
				nonce: nasoAdminData.nonce
			}, function(response) {
				$btn.prop('disabled', false).html(origHtml);
				if (response && response.success) {
					alert(response.data.message || 'Cache rebuilt successfully!');
				} else {
					alert('Failed to rebuild cache. Please try again.');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(origHtml);
				alert('Server communication error.');
			});
		});

		// 3. Quick GEO Audit on Dashboard
		$('#naso-run-quick-audit').on('click', function(e) {
			e.preventDefault();
			var postId = $('#naso_quick_post_id').val();
			if (!postId) {
				alert('Please select a post to audit.');
				return;
			}

			var $btn = $(this);
			var origText = $btn.html();
			var $resultContainer = $('#naso-quick-audit-result');

			$btn.prop('disabled', true).text(nasoAdminData.running || 'Scanning...');
			$resultContainer.slideUp();

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_run_audit',
				post_id: postId,
				nonce: nasoAdminData.nonce
			}, function(response) {
				$btn.prop('disabled', false).html(origText);

				if (response && response.success && response.data) {
					var data = response.data;
					var html = '<div class="notice notice-info inline" style="margin: 0; padding: 15px;">';
					html += '<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">';
					html += '<div><strong>' + escapeHtml(data.title) + '</strong> &mdash; ' + (data.word_count || 0) + ' words</div>';
					html += '<div><span class="naso-badge ' + escapeHtml(data.badge_class) + '">' + escapeHtml(data.rating) + ' (Score: ' + data.score + '/100)</span></div>';
					html += '</div>';

					if (data.recommendations && data.recommendations.length > 0) {
						html += '<p style="margin: 10px 0 5px 0;"><strong>Top Recommendations:</strong></p><ul style="margin:0; padding-left:20px;">';
						$.each(data.recommendations.slice(0, 3), function(idx, rec) {
							html += '<li>' + escapeHtml(rec) + '</li>';
						});
						html += '</ul>';
					}

					var baseAuditUrl = (typeof nasoAdminData !== 'undefined' && nasoAdminData.auditTabUrl)
						? nasoAdminData.auditTabUrl
						: adminUrl('admin.php?page=nexura-ai-search-audit');

					var fullAuditUrl = baseAuditUrl + (baseAuditUrl.indexOf('?') !== -1 ? '&' : '?') + 'post_id=' + postId;

					html += '<div style="margin-top:14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">';
					html += '<a href="' + fullAuditUrl + '" class="naso-btn-primary" style="font-size:13px; min-height:34px; padding:6px 16px; text-decoration:none;"><span class="dashicons dashicons-arrow-right-alt2"></span> View Full Breakdown in Audit Tab</a>';
					if (typeof nasoAdminData !== 'undefined' && nasoAdminData.adminPostUrl && nasoAdminData.pdfNonce) {
						var quickPdfUrl = nasoAdminData.adminPostUrl + '?action=naso_export_pdf&post_id=' + postId + '&nonce=' + nasoAdminData.pdfNonce;
						html += '<a href="' + quickPdfUrl + '" target="_blank" class="naso-btn-secondary" style="font-size:13px; min-height:34px; padding:6px 14px; text-decoration:none;"><span class="dashicons dashicons-media-document"></span> Export PDF</a>';
					}
					html += '</div></div>';

					$resultContainer.html(html).slideDown();
				} else {
					alert(response.data ? response.data.message : 'Audit failed.');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(origText);
				alert('Failed to connect to server.');
			});
		});

		// 4. Dedicated GEO Content Audit Page Runner
		$('#naso-start-audit-btn').on('click', function(e) {
			e.preventDefault();
			var postId = $('#naso_audit_post_select').val();
			if (!postId) {
				alert('Please choose a post or page from the dropdown.');
				return;
			}

			var $btn = $(this);
			var $spinner = $('#naso-audit-spinner');
			var $results = $('#naso-audit-results');

			$btn.prop('disabled', true);
			$spinner.addClass('is-active');
			$results.fadeOut(200);

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_run_audit',
				post_id: postId,
				nonce: nasoAdminData.nonce
			}, function(response) {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');

				if (response && response.success && response.data) {
					renderAuditResults(response.data);
					$results.fadeIn(300);
				} else {
					alert(response.data ? response.data.message : 'Audit scan encountered an error.');
				}
			}).fail(function() {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');
				alert('Server communication error.');
			});
		});

		// 5. Dismiss Upsell Notice
		$(document).on('click', '#naso-upsell-notice .notice-dismiss', function() {
			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_dismiss_upsell',
				nonce: nasoAdminData.nonce
			});
		});

		// Helper to render audit response
		function renderAuditResults(data) {
			$('#naso-audit-score').text(data.score);
			$('#naso-audit-title').text(data.title);
			$('#naso-audit-badge')
				.attr('class', 'naso-badge ' + data.badge_class)
				.text(data.rating);
			$('#naso-audit-word-count').text(data.word_count + ' total words');

			// Breakdown grid
			var $breakdownGrid = $('#naso-audit-breakdown');
			$breakdownGrid.empty();

			if (data.breakdown) {
				$.each(data.breakdown, function(key, item) {
					var cardHtml = '<div class="naso-breakdown-card">';
					cardHtml += '<div class="naso-breakdown-card-header">';
					cardHtml += '<span class="naso-breakdown-title">' + escapeHtml(item.title) + '</span>';
					cardHtml += '<span class="naso-breakdown-score">' + item.score + ' / ' + item.max + '</span>';
					cardHtml += '</div>';
					cardHtml += '<p class="naso-breakdown-status">' + escapeHtml(item.status) + '</p>';
					cardHtml += '</div>';
					$breakdownGrid.append(cardHtml);
				});
			}

			// Recommendations list
			var $recList = $('#naso-audit-recommendations');
			$recList.empty();

			if (data.recommendations && data.recommendations.length > 0) {
				$.each(data.recommendations, function(i, rec) {
					$recList.append('<li><span class="dashicons dashicons-arrow-right-alt2" style="color:#2271b1;vertical-align:middle;"></span> ' + escapeHtml(rec) + '</li>');
				});
			} else {
				$recList.append('<li style="color:#00a32a;"><span class="dashicons dashicons-yes"></span> Excellent! No immediate GEO issues found for this content.</li>');
			}

			// Configure Action Slot: Show Fully Optimized badge OR 1-Click AI Fix Prompt button
			var activePostId = data.id || $('#naso_audit_post_select').val();
			var $actionSlot = $('#naso-audit-action-slot');

			if (parseInt(data.score, 10) >= 100 || !data.recommendations || data.recommendations.length === 0) {
				$actionSlot.html('<span id="naso-audit-optimized-badge" class="naso-badge naso-badge-optimal" style="padding: 7px 14px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; background: #e7f8ed; color: #007a22; border: 1px solid #68de7c;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a; font-size: 18px; width: 18px; height: 18px;"></span> Fully Optimized (100/100)</span>');
			} else {
				$actionSlot.html('<button type="button" id="naso-audit-ai-prompt-btn" class="naso-btn-primary naso-trigger-ai-prompt" data-id="' + escapeHtml(activePostId) + '"><span class="dashicons dashicons-rest-api"></span> <span>1-Click AI Fix Prompt</span></button>');
			}

			// Configure PDF Export button
			var activePostId = data.id || $('#naso_audit_post_select').val();
			if (typeof nasoAdminData !== 'undefined' && nasoAdminData.adminPostUrl && nasoAdminData.pdfNonce && activePostId) {
				var pdfExportUrl = nasoAdminData.adminPostUrl + '?action=naso_export_pdf&post_id=' + activePostId + '&nonce=' + nasoAdminData.pdfNonce;
				$('#naso-audit-pdf-btn').attr('href', pdfExportUrl).css('display', 'inline-flex');
			}
		}

		// 6. Auto-run GEO audit if post_id is provided in URL
		var preselectedPostId = $('#naso_audit_post_select').val();
		if (preselectedPostId && window.location.search.indexOf('post_id=') !== -1) {
			$('#naso-start-audit-btn').trigger('click');
		}

		// 7. 1-Click AI Fix Prompt Modal Handler (from Tables, Meta Box, or Audit Tab)
		$(document).on('click', '.naso-trigger-ai-prompt', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var postId = $btn.data('id') || $('#naso_audit_post_select').val() || $('.naso-metabox-container').data('post-id');

			if (!postId) {
				alert('Please choose or scan a post first.');
				return;
			}

			var origHtml = $btn.html();
			$btn.prop('disabled', true).text('Generating Prompt...');

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_get_ai_fix_prompt',
				post_id: postId,
				nonce: nasoAdminData.nonce
			}, function(response) {
				$btn.prop('disabled', false).html(origHtml);

				if (response && response.success && response.data) {
					openAiPromptModal(response.data);
				} else {
					alert(response.data ? response.data.message : 'Could not generate AI prompt.');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(origHtml);
				alert('Server communication error.');
			});
		});

		// 8. Meta Box Live Scanner (Post/Product Edit Screens)
		$(document).on('click', '.naso-mb-scan-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var postId = $btn.data('post-id');
			if (!postId) return;

			var origHtml = $btn.html();
			$btn.prop('disabled', true).text(nasoAdminData.running || 'Scanning...');

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_run_audit',
				post_id: postId,
				nonce: nasoAdminData.nonce
			}, function(response) {
				$btn.prop('disabled', false).html(origHtml);

				if (response && response.success && response.data) {
					var data = response.data;
					var $container = $btn.closest('.naso-metabox-container');

					// Update score & colors
					var scoreColor = (data.score >= 80) ? '#00a32a' : ((data.score >= 50) ? '#dba617' : '#d63638');
					$container.find('.naso-mb-score').text(data.score).css('color', scoreColor);
					$container.find('.naso-mb-header h4').text(data.rating);

					// Update findings
					var $issuesList = $('<ul class="naso-recommendations-list" style="margin: 0; padding-left: 20px;"></ul>');
					if (data.recommendations && data.recommendations.length > 0) {
						$.each(data.recommendations, function(i, rec) {
							$issuesList.append('<li style="margin-bottom: 6px; font-size: 13px; color: #2c3338;"><strong>&bull;</strong> ' + escapeHtml(rec) + '</li>');
						});
						$container.find('.naso-mb-issues-container').html($issuesList);
					} else {
						$container.find('.naso-mb-issues-container').html('<p style="margin: 0; color: #00a32a; font-weight: 500;">✓ Excellent! Satisfies core Generative Engine Optimization benchmarks.</p>');
					}

					// Update Meta Box AI Fix button vs Fully Optimized badge
					var $mbSlot = $container.find('.naso-mb-action-slot');
					if ($mbSlot.length) {
						if (parseInt(data.score, 10) >= 100 || !data.recommendations || data.recommendations.length === 0) {
							$mbSlot.html('<span class="naso-badge naso-badge-optimal naso-mb-optimized-badge" style="padding: 6px 12px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; background: #e7f8ed; color: #007a22; border: 1px solid #68de7c;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> Fully Optimized</span>');
						} else {
							$mbSlot.html('<button type="button" class="naso-btn-secondary naso-trigger-ai-prompt" data-id="' + data.id + '"><span class="dashicons dashicons-rest-api" style="color: #2271b1;"></span> <span>1-Click AI Fix Prompt</span></button>');
						}
					}
				} else {
					alert(response.data ? response.data.message : 'Scan failed.');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(origHtml);
				alert('Server communication error.');
			});
		});

		// Render AI Prompt Modal
		function openAiPromptModal(data) {
			$('#naso-ai-modal-backdrop').remove();

			var isPro = (typeof nasoAdminData !== 'undefined' && nasoAdminData.isPro == 1);
			var hasProAi = (typeof nasoAdminData !== 'undefined' && nasoAdminData.hasProAi == 1);
			var proAgentUrl = (typeof nasoAdminData !== 'undefined' && nasoAdminData.proAgentUrl) ? nasoAdminData.proAgentUrl : '#';
			var upgradeUrl = (typeof nasoAdminData !== 'undefined' && nasoAdminData.upgradeUrl) ? nasoAdminData.upgradeUrl : 'https://nexurasecurity.com/nexura-ai-search-optimizer/pricing/';

			var modalHtml = '<div id="naso-ai-modal-backdrop" class="naso-modal-backdrop">';
			modalHtml += '<div class="naso-modal" style="max-width:760px;">';
			modalHtml += '<div class="naso-modal-header">';
			modalHtml += '<h3><span class="dashicons dashicons-rest-api" style="color:#2271b1;font-size:20px;width:20px;height:20px;"></span> 1-Click AI Fix &mdash; ' + escapeHtml(data.title) + '</h3>';
			modalHtml += '<button type="button" class="naso-modal-close" id="naso-close-ai-modal" title="Close">&times;</button>';
			modalHtml += '</div>';

			modalHtml += '<div class="naso-modal-body">';
			modalHtml += '<div id="naso-ai-status-notice"></div>';

			if (parseInt(data.score, 10) >= 100) {
				modalHtml += '<div style="background:#e7f8ed; border:1px solid #68de7c; border-left:4px solid #00a32a; padding:10px 14px; border-radius:6px; margin-bottom:12px; font-size:13px; color:#1d2327;">';
				modalHtml += '<span class="dashicons dashicons-yes-alt" style="color:#00a32a; font-size:18px; width:18px; height:18px; vertical-align:text-bottom;"></span> <strong>Perfect Score (100/100):</strong> This content already satisfies all Princeton University GEO benchmarks with 0 weaknesses! No fixes are needed, but you can still use the prompt below if you wish to expand or update it.';
				modalHtml += '</div>';
			} else if (isPro && !hasProAi) {
				modalHtml += '<div style="background:#f0f6fc; border:1px solid #c8d8f8; border-left:4px solid #2271b1; padding:10px 14px; border-radius:6px; margin-bottom:12px; font-size:13px; color:#1d2327; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">';
				modalHtml += '<div style="display:flex; align-items:center; gap:8px;"><span class="dashicons dashicons-yes-alt" style="color:#00a32a; font-size:18px; width:18px; height:18px;"></span><span><strong>Nexura Pro Active:</strong> Connect your OpenAI or Claude API key to enable 1-Click Auto-Fix!</span></div>';
				modalHtml += '<a href="' + proAgentUrl + '" target="_blank" style="font-weight:600; color:#2271b1; text-decoration:underline;">Open AI Agent Settings &rarr;</a>';
				modalHtml += '</div>';
			} else if (!isPro) {
				modalHtml += '<div style="background:#fcf9e8; border:1px solid #fae8a4; border-left:4px solid #dba617; padding:10px 14px; border-radius:6px; margin-bottom:12px; font-size:13px; color:#1d2327;">';
				modalHtml += '<span class="dashicons dashicons-star-filled" style="color:#e2b007; font-size:16px; width:16px; height:16px; vertical-align:text-bottom;"></span> <strong>Pro Feature:</strong> Want 1-click automated rewriting with AI? <a href="' + upgradeUrl + '" style="color:#2271b1; font-weight:600; text-decoration:underline;">Upgrade to Nexura Pro</a> to connect your AI Agent.';
				modalHtml += '</div>';
			}

			modalHtml += '<p style="margin:0 0 10px 0; font-size:13px; color:#50575e;">' + (hasProAi ? 'Use your connected <strong>AI Agent</strong> to auto-fix this content, or copy the Princeton GEO prompt manually:' : 'Paste this prompt directly into <strong>ChatGPT</strong>, <strong>Claude</strong>, or <strong>Perplexity</strong> to rewrite this content according to Princeton University Generative Engine Optimization (GEO) standards:') + '</p>';
			modalHtml += '<div id="naso-prompt-view-wrapper">';
			modalHtml += '<textarea id="naso-ai-prompt-content" class="naso-prompt-textarea" style="height:210px;" readonly>' + escapeHtml(data.prompt) + '</textarea>';
			modalHtml += '</div>';
			modalHtml += '<div id="naso-rewritten-view-wrapper" style="display:none; margin-top:12px;">';
			modalHtml += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><strong>✅ Rewritten GEO Content (Review & Apply):</strong><span class="naso-badge naso-badge-success">AI Optimized</span></div>';
			modalHtml += '<textarea id="naso-ai-rewritten-content" class="naso-prompt-textarea" style="height:220px; background:#f6fff8; border-color:#00a32a;"></textarea>';
			modalHtml += '</div>';
			modalHtml += '</div>';

			modalHtml += '<div class="naso-modal-footer">';
			modalHtml += '<div class="naso-copy-toast" id="naso-copy-toast"><span class="dashicons dashicons-yes-alt" style="color:#00a32a;font-size:18px;width:18px;height:18px;"></span> Copied to clipboard! Ready to paste into AI.</div>';
			modalHtml += '<div style="margin-left:auto; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">';
			modalHtml += '<button type="button" class="naso-btn-secondary" id="naso-dismiss-modal-btn">Close</button>';
			modalHtml += '<button type="button" class="naso-btn-secondary" id="naso-copy-prompt-btn"><span class="dashicons dashicons-clipboard"></span> Copy Prompt</button>';

			if (hasProAi) {
				modalHtml += '<button type="button" class="naso-btn-primary" id="naso-run-auto-ai-fix-btn" data-id="' + data.id + '" style="background:#2271b1; font-weight:600;"><span class="dashicons dashicons-superhero-alt"></span> ⚡ Auto-Fix with AI (1-Click)</button>';
				modalHtml += '<button type="button" class="naso-btn-success" id="naso-apply-rewritten-btn" data-id="' + data.id + '" style="display:none; font-weight:600;"><span class="dashicons dashicons-yes-alt"></span> Apply to Post & Re-Score</button>';
			} else if (isPro) {
				modalHtml += '<a href="' + proAgentUrl + '" target="_blank" class="naso-btn-primary" style="background:#2271b1; text-decoration:none; display:inline-flex; align-items:center; gap:5px; font-weight:600;"><span class="dashicons dashicons-admin-generic"></span> Connect AI Key (Enable 1-Click Fix)</a>';
			} else {
				modalHtml += '<a href="' + upgradeUrl + '" class="naso-btn-primary" style="background:#2271b1; text-decoration:none; display:inline-flex; align-items:center; gap:5px; font-weight:600;"><span class="dashicons dashicons-star-filled" style="color:#e2b007; font-size:16px; width:16px; height:16px;"></span> Upgrade to Pro (1-Click AI Fix)</a>';
			}

			modalHtml += '</div>';
			modalHtml += '</div>';

			modalHtml += '</div></div>';

			$('body').append(modalHtml);
		}

		// Close modal
		$(document).on('click', '#naso-close-ai-modal, #naso-dismiss-modal-btn', function() {
			$('#naso-ai-modal-backdrop').fadeOut(150, function() { $(this).remove(); });
		});

		$(document).on('click', '#naso-ai-modal-backdrop', function(e) {
			if ($(e.target).is('#naso-ai-modal-backdrop')) {
				$('#naso-ai-modal-backdrop').fadeOut(150, function() { $(this).remove(); });
			}
		});

		// Copy Prompt to Clipboard
		$(document).on('click', '#naso-copy-prompt-btn', function() {
			var $txt = $('#naso-ai-prompt-content');
			$txt.select();
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText($txt.val()).then(function() {
					showCopyToast();
				});
			} else {
				document.execCommand('copy');
				showCopyToast();
			}
		});

		// 8. Quick In-Place Row Scan
		$(document).on('click', '.naso-trigger-row-scan', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var $cell = $btn.closest('.naso-table-cell');
			var postId = $btn.data('id') || $cell.data('post-id');

			if (!postId) return;

			var origHtml = $cell.html();
			$cell.html('<span class="spinner is-active" style="float:none; margin:0 4px 0 0; vertical-align:middle;"></span> <span style="font-size:11px; color:#50575e;">Scanning GEO...</span>');

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_scan_batch_ajax',
				specific_ids: [postId],
				nonce: nasoAdminData.nonce
			}, function(response) {
				if (response && response.success && response.data && response.data.results && response.data.results.length > 0) {
					var item = response.data.results[0];
					$cell.html(item.cell_html);
					$cell.addClass('naso-table-cell-highlight');
					setTimeout(function() {
						$cell.removeClass('naso-table-cell-highlight');
					}, 1500);
				} else {
					$cell.html(origHtml);
					alert('Scan failed. Please try again.');
				}
			}).fail(function() {
				$cell.html(origHtml);
				alert('Server communication error.');
			});
		});

		// 9. One-Click Batch Scan in List Table Navigation
		$(document).on('click', '.naso-btn-scan-all-table', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var postType = $btn.data('post-type') || '';
			var $progress = $btn.siblings('.naso-table-scan-progress');

			// Collect all unscanned cell IDs on the current visible screen
			var unscannedIds = [];
			$('.naso-table-cell').each(function() {
				var $cell = $(this);
				var text = $cell.find('.naso-badge').text().toLowerCase();
				if (text.indexOf('not scanned') !== -1) {
					var pid = $cell.data('post-id');
					if (pid) unscannedIds.push(pid);
				}
			});

			if (unscannedIds.length === 0) {
				alert('All items on this page are already scanned!');
				return;
			}

			var totalItems = unscannedIds.length;
			var processedCount = 0;

			$btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 4px 0 0; vertical-align:middle;"></span> Scanning...');
			$progress.show().text('0 of ' + totalItems + ' scanned');

			function processNextBatch() {
				if (unscannedIds.length === 0) {
					$btn.html('<span class="dashicons dashicons-yes-alt" style="color:#00a32a; vertical-align:text-bottom;"></span> All Scanned!');
					$progress.text('✅ Complete (' + totalItems + ' items)').css('color', '#00a32a');
					setTimeout(function() {
						$progress.fadeOut();
					}, 4000);
					return;
				}

				var batch = unscannedIds.splice(0, 4);

				// Put spinner on those cells
				$.each(batch, function(i, id) {
					var $c = $('.naso-table-cell[data-post-id="' + id + '"]');
					$c.find('.naso-badge').css('opacity', '0.6').text('Scanning...');
				});

				$.post(nasoAdminData.ajaxUrl, {
					action: 'naso_scan_batch_ajax',
					specific_ids: batch,
					post_type: postType,
					nonce: nasoAdminData.nonce
				}, function(response) {
					if (response && response.success && response.data && response.data.results) {
						$.each(response.data.results, function(i, item) {
							var $c = $('.naso-table-cell[data-post-id="' + item.id + '"]');
							$c.html(item.cell_html);
							$c.addClass('naso-table-cell-highlight');
							setTimeout(function() {
								$c.removeClass('naso-table-cell-highlight');
							}, 1500);
						});

						processedCount += response.data.results.length;
						$progress.text(processedCount + ' of ' + totalItems + ' scanned (' + Math.round((processedCount / totalItems) * 100) + '%)');
					}

					// Small delay between batches to protect low-end servers
					setTimeout(processNextBatch, 300);
				}).fail(function() {
					$progress.text('Batch paused due to network error. Retrying...').css('color', '#d63638');
					setTimeout(processNextBatch, 2000);
				});
			}

			processNextBatch();
		});

		// 10. Global Auto-Scan Notice Runner
		$(document).on('click', '.naso-btn-scan-all-global', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var total = parseInt($btn.data('total'), 10) || 1;
			var $notice = $btn.closest('.naso-auto-scan-notice');
			var $progressBar = $notice.find('.naso-global-scan-progress');
			var $fill = $notice.find('.naso-progress-fill');
			var $statusText = $notice.find('.naso-scan-status-text');
			var $percentText = $notice.find('.naso-scan-percent-text');

			$btn.prop('disabled', true).text('Scanning in progress...');
			$progressBar.slideDown();

			function runGlobalBatch() {
				$.post(nasoAdminData.ajaxUrl, {
					action: 'naso_scan_batch_ajax',
					limit: 5,
					nonce: nasoAdminData.nonce
				}, function(response) {
					if (response && response.success && response.data) {
						var remaining = response.data.remaining_total || 0;
						var processedSoFar = Math.max(0, total - remaining);
						var percent = Math.min(100, Math.round((processedSoFar / total) * 100));

						$fill.css('width', percent + '%');
						$percentText.text(percent + '%');
						$statusText.text('Scanning content for AI Search engines... (' + processedSoFar + ' / ' + total + ')');

						// If visible table cells are on screen, update them
						if (response.data.results) {
							$.each(response.data.results, function(i, item) {
								var $c = $('.naso-table-cell[data-post-id="' + item.id + '"]');
								if ($c.length) {
									$c.html(item.cell_html);
									$c.addClass('naso-table-cell-highlight');
								}
							});
						}

						if (response.data.is_complete || remaining <= 0) {
							$fill.css('width', '100%').css('background', '#00a32a');
							$percentText.text('100%');
							$statusText.text('✅ Scan complete! All content is now analyzed for AI Search.').css('color', '#00a32a');
							$btn.text('Done!');
							setTimeout(function() {
								location.reload();
							}, 1500);
							return;
						}

						setTimeout(runGlobalBatch, 350);
					} else {
						$statusText.text('Paused. Retrying batch...');
						setTimeout(runGlobalBatch, 2000);
					}
				}).fail(function() {
					$statusText.text('Connection interrupted. Retrying batch...');
					setTimeout(runGlobalBatch, 2000);
				});
			}

			runGlobalBatch();
		});

		// 11. Dismiss Scan Notice Banner
		$(document).on('click', '.naso-btn-dismiss-scan-notice', function(e) {
			e.preventDefault();
			var $notice = $(this).closest('.naso-auto-scan-notice');
			$notice.fadeOut(200);
			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_dismiss_scan_notice',
				nonce: nasoAdminData.nonce
			});
		});

		// 12. 1-Click Auto-Fix with AI Agent (Pro)
		$(document).on('click', '#naso-run-auto-ai-fix-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var postId = $btn.data('id');
			var $status = $('#naso-ai-status-notice');
			var $promptWrapper = $('#naso-prompt-view-wrapper');
			var $rewrittenWrapper = $('#naso-rewritten-view-wrapper');
			var $rewrittenTxt = $('#naso-ai-rewritten-content');
			var $applyBtn = $('#naso-apply-rewritten-btn');

			if (!postId) return;

			var origHtml = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 4px 0 0; vertical-align:middle;"></span> AI Agent is Rewriting...');
			$status.html('<div class="notice notice-info inline" style="margin:0 0 12px 0; padding:10px;"><span class="spinner is-active" style="float:none; margin:0 4px 0 0; vertical-align:middle;"></span> Connecting to your AI Model. Restructuring with Princeton GEO benchmarks (BLUF, data density, schema)...</div>');

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_pro_run_ai_fix',
				post_id: postId,
				nonce: nasoAdminData.nonce
			}, function(response) {
				$btn.prop('disabled', false).html(origHtml);

				if (response && response.success && response.data) {
					var data = response.data;
					$rewrittenTxt.val(data.rewritten_content);
					$promptWrapper.slideUp(150);
					$rewrittenWrapper.slideDown(200);

					if (data.applied) {
						$status.html('<div class="notice notice-success inline" style="margin:0 0 12px 0; padding:10px;"><strong>✅ Success!</strong> Content rewritten & updated in your post. New Score: <strong>' + (data.new_score || 88) + '/100</strong> (' + escapeHtml(data.new_rating || 'Optimal for AI') + ')</div>');
						$btn.hide();
						$applyBtn.show().text('✅ Updated Successfully').prop('disabled', true);

						// Update table cell if visible on list screen
						var $cell = $('.naso-table-cell[data-post-id="' + postId + '"]');
						if ($cell.length) {
							$cell.find('.naso-badge').removeClass('naso-badge-danger naso-badge-warning').addClass('naso-badge-success').text((data.new_score || 88) + ' / 100');
						}
					} else {
						$status.html('<div class="notice notice-info inline" style="margin:0 0 12px 0; padding:10px;"><strong>✅ Content Rewritten!</strong> Review the optimized GEO content below, then click "Apply to Post" to save.</div>');
						$btn.hide();
						$applyBtn.show();
					}
				} else {
					var err = (response && response.data && response.data.message) ? response.data.message : 'AI rewrite failed.';
					$status.html('<div class="notice notice-error inline" style="margin:0 0 12px 0; padding:10px;">' + escapeHtml(err) + '</div>');
				}
			}).fail(function() {
				$btn.prop('disabled', false).html(origHtml);
				$status.html('<div class="notice notice-error inline" style="margin:0 0 12px 0; padding:10px;">Server timeout or connection failed.</div>');
			});
		});

		// 13. Apply Rewritten Content to Post (Pro)
		$(document).on('click', '#naso-apply-rewritten-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var postId = $btn.data('id');
			var content = $('#naso-ai-rewritten-content').val();
			var $status = $('#naso-ai-status-notice');

			if (!postId || !content) return;

			$btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 4px 0 0; vertical-align:middle;"></span> Updating...');

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_pro_apply_rewritten_content',
				post_id: postId,
				content: content,
				nonce: nasoAdminData.nonce
			}, function(response) {
				if (response && response.success && response.data) {
					var data = response.data;
					$btn.html('✅ Applied & Scored (' + data.new_score + '/100)');
					$status.html('<div class="notice notice-success inline" style="margin:0 0 12px 0; padding:10px;"><strong>✅ Success!</strong> Post updated with GEO-optimized content. New Score: <strong>' + data.new_score + '/100</strong></div>');

					// Update table cell badge if visible on list screen
					var $cell = $('.naso-table-cell[data-post-id="' + postId + '"]');
					if ($cell.length) {
						$cell.find('.naso-badge').attr('class', 'naso-badge ' + data.new_badge).text(data.new_score + ' / 100');
						$cell.addClass('naso-table-cell-highlight');
					}

					// Update metabox if visible on edit screen
					var $mbScore = $('.naso-mb-score');
					if ($mbScore.length) {
						$mbScore.text(data.new_score).css('color', '#00a32a');
					}
				} else {
					$btn.prop('disabled', false).text('Apply to Post');
					alert('Failed to update post.');
				}
			}).fail(function() {
				$btn.prop('disabled', false).text('Apply to Post');
				alert('Server communication error.');
			});
		});

		// 14. Pro AI Agent Settings Page Helpers
		var providerModels = {
			openai: [
				{ id: 'gpt-4o-mini', name: 'GPT-4o Mini (Fast & Cost-Effective - Recommended)' },
				{ id: 'gpt-4o', name: 'GPT-4o (Most Capable & Authoritative)' },
				{ id: 'gpt-4-turbo', name: 'GPT-4 Turbo' }
			],
			claude: [
				{ id: 'claude-3-5-sonnet-latest', name: 'Claude 3.5 Sonnet (Highest Writing Intelligence - Recommended)' },
				{ id: 'claude-3-5-haiku-latest', name: 'Claude 3.5 Haiku (Lightning Fast)' }
			],
			gemini: [
				{ id: 'gemini-1.5-flash', name: 'Gemini 1.5 Flash (Ultra Fast & Free Tier Available)' },
				{ id: 'gemini-1.5-pro', name: 'Gemini 1.5 Pro (Deep Context)' }
			]
		};

		function populateAiModels(provider, selectedModel) {
			var $select = $('#naso_ai_model');
			if (!$select.length) return;

			$select.empty();
			var models = providerModels[provider] || providerModels.openai;

			$.each(models, function(i, m) {
				var isSel = (m.id === selectedModel);
				$select.append($('<option>', {
					value: m.id,
					text: m.name,
					selected: isSel
				}));
			});
		}

		if ($('#naso_ai_provider').length) {
			var initProvider = $('#naso_ai_provider').val();
			var currentModel = $('#naso_ai_model').data('current') || '';
			populateAiModels(initProvider, currentModel);

			$('#naso_ai_provider').on('change', function() {
				var p = $(this).val();
				$('.naso-provider-card').removeClass('is-selected').filter('[data-provider="' + p + '"]').addClass('is-selected');
				$('.naso-key-hint').hide();
				$('#naso-key-hint-' + p).show();
				populateAiModels(p, '');
			});

			$('#naso-toggle-key-visibility').on('click', function(e) {
				e.preventDefault();
				var $input = $('#naso_ai_api_key');
				var type = $input.attr('type') === 'password' ? 'text' : 'password';
				$input.attr('type', type);
				$(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
			});

			$('#naso-save-ai-settings-btn').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var origText = $btn.text();
				$btn.prop('disabled', true).text('Saving...');

				$.post(nasoAdminData.ajaxUrl, {
					action: 'naso_pro_save_ai_settings',
					provider: $('#naso_ai_provider').val(),
					api_key: $('#naso_ai_api_key').val(),
					model: $('#naso_ai_model').val(),
					auto_update: $('input[name="auto_update"]').is(':checked') ? 1 : 0,
					nonce: nasoAdminData.nonce
				}, function(response) {
					$btn.prop('disabled', false).text(origText);
					if (response && response.success) {
						alert(response.data.message || 'Saved successfully!');
						location.reload();
					} else {
						alert((response && response.data && response.data.message) ? response.data.message : 'Error saving settings.');
					}
				}).fail(function() {
					$btn.prop('disabled', false).text(origText);
					alert('Server communication error.');
				});
			});

			$('#naso-test-ai-connection-btn').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var $status = $('#naso-test-connection-status');
				var origHtml = $btn.html();

				$btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 4px 0 0; vertical-align:middle;"></span> Testing...');
				$status.text('').css('color', '#50575e');

				$.post(nasoAdminData.ajaxUrl, {
					action: 'naso_pro_test_ai_connection',
					provider: $('#naso_ai_provider').val(),
					api_key: $('#naso_ai_api_key').val(),
					model: $('#naso_ai_model').val(),
					nonce: nasoAdminData.nonce
				}, function(response) {
					$btn.prop('disabled', false).html(origHtml);
					if (response && response.success) {
						$status.text('✅ ' + response.data.message).css('color', '#00a32a');
					} else {
						var msg = (response && response.data && response.data.message) ? response.data.message : 'Connection failed.';
						$status.text('❌ ' + msg).css('color', '#d63638');
					}
				}).fail(function() {
					$btn.prop('disabled', false).html(origHtml);
					$status.text('❌ Network error testing connection.').css('color', '#d63638');
				});
			});
		}

		// -------------------------------------------------------------
		// Pro Bulk GEO Audit Batch Runner
		// -------------------------------------------------------------
		var bulkOffset = 0;
		var bulkLimit = 5;
		var isBulkRunning = false;

		$('#naso-start-bulk-scan').on('click', function(e) {
			e.preventDefault();
			if (isBulkRunning) return;

			isBulkRunning = true;
			bulkOffset = 0;
			$(this).prop('disabled', true).text('Scanning Library...');
			$('#naso-bulk-spinner').addClass('is-active');
			$('#naso-progress-container').slideDown();
			$('#naso-no-results-row').remove();
			$('#naso-bulk-tbody').empty();

			runBulkBatch();
		});

		function runBulkBatch() {
			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_pro_bulk_audit_batch',
				offset: bulkOffset,
				limit: bulkLimit,
				nonce: nasoAdminData.nonce
			}, function(res) {
				if (res && res.success && res.data) {
					var data = res.data;
					var items = data.items || [];

					$.each(items, function(i, item) {
						var rowHtml = '<tr>' +
							'<td><strong><a href="' + item.url + '" target="_blank">' + escapeHtml(item.title) + '</a></strong></td>' +
							'<td>' + item.word_count + '</td>' +
							'<td><strong>' + item.score + ' / 100</strong></td>' +
							'<td><span class="naso-badge ' + item.badge_class + '">' + escapeHtml(item.rating) + '</span></td>' +
							'<td><a href="' + item.url + '" class="button button-small" target="_blank">View</a></td>' +
							'</tr>';
						$('#naso-bulk-tbody').append(rowHtml);
					});

					$('#naso-progress-percent').text(data.percentage + '%');
					$('#naso-progress-bar').css('width', data.percentage + '%');
					$('#naso-progress-label').text('Processed ' + data.processed + ' of ' + data.total + ' items...');

					if (!data.is_complete && items.length > 0) {
						bulkOffset += bulkLimit;
						runBulkBatch();
					} else {
						finishBulkScan();
					}
				} else {
					alert('Batch processing error.');
					finishBulkScan();
				}
			}).fail(function() {
				alert('Server connection error.');
				finishBulkScan();
			});
		}

		function finishBulkScan() {
			isBulkRunning = false;
			$('#naso-start-bulk-scan').prop('disabled', false).text('Start Full Library Scan');
			$('#naso-bulk-spinner').removeClass('is-active');
			$('#naso-clear-bulk-scan').show();
		}

		$('#naso-clear-bulk-scan').on('click', function(e) {
			e.preventDefault();
			if (!confirm('Are you sure you want to clear stored bulk scan results?')) return;

			$.post(nasoAdminData.ajaxUrl, {
				action: 'naso_pro_clear_bulk_results',
				nonce: nasoAdminData.nonce
			}, function(res) {
				if (res && res.success) {
					location.reload();
				}
			});
		});

		// -------------------------------------------------------------
		// Pro AI Agent Provider Card Selector
		// -------------------------------------------------------------
		$('.naso-provider-card').on('click', function() {
			var provider = $(this).data('provider');
			$('.naso-provider-card').removeClass('is-selected');
			$(this).addClass('is-selected');
			$('#naso_ai_provider').val(provider).trigger('change');
		});

		// -------------------------------------------------------------
		// 14. Autocomplete Search to Exclude Posts / Pages
		// -------------------------------------------------------------
		var searchTimeout = null;
		var $searchInput  = $('#naso-exclude-search-input');
		var $resultsList  = $('#naso-exclude-search-results');
		var $hiddenInput  = $('#naso_excluded_post_ids');
		var $tagsContainer = $('#naso-excluded-tags');
		var $spinner      = $('#naso-exclude-search-spinner');

		function updateExcludedHiddenInput() {
			var ids = [];
			$tagsContainer.find('.naso-tag-pill').each(function() {
				var id = $(this).data('id');
				if (id) {
					ids.push(id);
				}
			});
			$hiddenInput.val(ids.join(','));
		}

		$searchInput.on('keyup input', function(e) {
			if ($(this).prop('disabled')) {
				return;
			}
			var term = $.trim($(this).val());

			// If escape pressed, close dropdown
			if (e.keyCode === 27) {
				$resultsList.hide();
				return;
			}

			if (term.length < 2) {
				$resultsList.hide().empty();
				$spinner.removeClass('is-active');
				return;
			}

			clearTimeout(searchTimeout);
			$spinner.addClass('is-active');

			searchTimeout = setTimeout(function() {
				$.get(nasoAdminData.ajaxUrl, {
					action: 'naso_search_posts',
					term: term,
					nonce: nasoAdminData.nonce
				}, function(response) {
					$spinner.removeClass('is-active');
					$resultsList.empty();

					if (response && response.success && response.data && response.data.results && response.data.results.length > 0) {
						var existingIds = ($hiddenInput.val() || '').split(',').map(function(id) {
							return $.trim(id);
						});

						$.each(response.data.results, function(i, item) {
							var isSelected = existingIds.indexOf(String(item.id)) !== -1;
							var $li = $('<li>')
								.attr('data-id', item.id)
								.attr('data-title', item.title)
								.attr('data-type', item.type);

							var titleSpan = $('<span>').text(item.title);
							var metaSpan = $('<span style="color:#646970; font-size:11px;">').text(' (' + item.type + ' • ID: ' + item.id + ')');

							if (isSelected) {
								$li.addClass('already-selected').css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
								metaSpan.append(' <strong style="color:#00a32a;">✓ Added</strong>');
							}

							$li.append(titleSpan).append(metaSpan);
							$resultsList.append($li);
						});

						$resultsList.show();
					} else {
						$resultsList.append('<li style="color:#646970; cursor:default; font-style:italic;">No matching posts or pages found.</li>').show();
					}
				}).fail(function() {
					$spinner.removeClass('is-active');
				});
			}, 250);
		});

		// Click suggestion item
		$resultsList.on('click', 'li:not(.already-selected)', function(e) {
			e.preventDefault();
			var id    = $(this).data('id');
			var title = $(this).data('title');
			var type  = $(this).data('type');

			if (!id) {
				return;
			}

			// Add pill safely
			var $pill = $('<span class="naso-tag-pill">').attr('data-id', id);
			var $bold = $('<strong>').text(title);
			var $typeSpan = $('<span style="color:#646970; font-size:11px;">').text(' (' + type + ' • ID: ' + id + ')');
			var $removeBtn = $('<button type="button" class="naso-tag-remove" style="background:none; border:none; padding:0 2px; cursor:pointer; color:#8c8f94; font-weight:bold; line-height:1; font-size:14px;" title="Remove">&times;</button>');

			$pill.append($bold).append(' ').append($typeSpan).append(' ').append($removeBtn);
			$tagsContainer.append($pill);
			updateExcludedHiddenInput();

			$searchInput.val('');
			$resultsList.hide().empty();
		});

		// Remove tag pill
		$tagsContainer.on('click', '.naso-tag-remove', function(e) {
			e.preventDefault();
			$(this).closest('.naso-tag-pill').remove();
			updateExcludedHiddenInput();
		});

		// Close dropdown when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.naso-post-picker').length) {
				$resultsList.hide();
			}
		});

		function showCopyToast() {
			$('#naso-copy-toast').fadeIn(200);
			setTimeout(function() {
				$('#naso-copy-toast').fadeOut(300);
			}, 3000);
		}

		function escapeHtml(string) {
			if (!string) return '';
			return String(string).replace(/[&<>"'`=\/]/g, function (s) {
				return {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#39;',
					'/': '&#x2F;',
					'`': '&#x60;',
					'=': '&#x3D;'
				}[s];
			});
		}

		function adminUrl(path) {
			return window.location.pathname + '?' + path.replace(/^.*\?/, '');
		}
	});

})(jQuery);
