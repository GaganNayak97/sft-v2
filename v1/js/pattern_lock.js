/**
 * Interactive 3x3 Pattern Lock & PIN Keypad Engine
 */

$(document).ready(function() {
  // --- PIN Keypad Handler ---
  let currentPin = '';

  function renderPinDisplay() {
    $('.pin-digit-box').each(function(i) {
      if (i < currentPin.length) {
        $(this).text('●').addClass('filled');
      } else {
        $(this).text('•').removeClass('filled');
      }
    });

    if (currentPin.length === 4) {
      handlePinComplete(currentPin);
    }
  }

  $('.pin-key[data-val]').on('click', function() {
    if (currentPin.length < 4) {
      currentPin += $(this).data('val');
      renderPinDisplay();
    }
  });

  $('#pinClearBtn').on('click', function() {
    currentPin = '';
    renderPinDisplay();
    $('#securityFeedbackMsg').empty();
  });

  $('#pinBackspaceBtn').on('click', function() {
    if (currentPin.length > 0) {
      currentPin = currentPin.slice(0, -1);
      renderPinDisplay();
      $('#securityFeedbackMsg').empty();
    }
  });

  function handlePinComplete(pin) {
    $('#securityFeedbackMsg').html('<span style="color: var(--secondary);">Verifying PIN...</span>');
    $.post('index.php?api=security', { action: 'verify_pin', pin: pin }, function(res) {
      if (res.success) {
        $('#securityFeedbackMsg').html('<span style="color: var(--success);">✅ Verified!</span>');
        setTimeout(() => {
          $('#securityLockModal').removeClass('active');
          currentPin = '';
          renderPinDisplay();
        }, 800);
      } else {
        $('#securityFeedbackMsg').html('<span style="color: var(--danger);">❌ ' + res.message + '</span>');
        setTimeout(() => {
          currentPin = '';
          renderPinDisplay();
        }, 1200);
      }
    }, 'json');
  }

  // --- Pattern Lock Engine ---
  function initPatternLock(containerId, svgId, polylineId, onComplete) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const svg = document.getElementById(svgId);
    const polyline = document.getElementById(polylineId);
    const nodes = container.querySelectorAll('.pattern-node');

    let isDrawing = false;
    let selectedNodes = [];

    function getNodeCenter(node) {
      const rect = node.getBoundingClientRect();
      const containerRect = container.getBoundingClientRect();
      return {
        x: rect.left + rect.width / 2 - containerRect.left,
        y: rect.top + rect.height / 2 - containerRect.top
      };
    }

    function updatePolyline(touchPoint) {
      let pointsStr = selectedNodes.map(node => {
        const center = getNodeCenter(node);
        return `${center.x},${center.y}`;
      }).join(' ');

      if (touchPoint && selectedNodes.length > 0) {
        pointsStr += ` ${touchPoint.x},${touchPoint.y}`;
      }

      polyline.setAttribute('points', pointsStr);
    }

    function handleSelectNode(node) {
      if (!selectedNodes.includes(node)) {
        selectedNodes.push(node);
        node.classList.add('selected');
        updatePolyline();
      }
    }

    function checkNodeUnderPoint(x, y) {
      nodes.forEach(node => {
        const rect = node.getBoundingClientRect();
        if (x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom) {
          handleSelectNode(node);
        }
      });
    }

    function startDraw(e) {
      isDrawing = true;
      selectedNodes = [];
      nodes.forEach(n => n.classList.remove('selected', 'error'));
      polyline.setAttribute('points', '');

      const point = e.touches ? e.touches[0] : e;
      checkNodeUnderPoint(point.clientX, point.clientY);
    }

    function moveDraw(e) {
      if (!isDrawing) return;
      e.preventDefault();

      const point = e.touches ? e.touches[0] : e;
      checkNodeUnderPoint(point.clientX, point.clientY);

      const containerRect = container.getBoundingClientRect();
      const currentPoint = {
        x: point.clientX - containerRect.left,
        y: point.clientY - containerRect.top
      };

      updatePolyline(currentPoint);
    }

    function endDraw() {
      if (!isDrawing) return;
      isDrawing = false;
      updatePolyline(); // Drop floating touch line

      if (selectedNodes.length > 0) {
        const patternSequence = selectedNodes.map(n => n.getAttribute('data-index')).join('-');
        if (onComplete) {
          onComplete(patternSequence, selectedNodes);
        }
      }
    }

    container.addEventListener('mousedown', startDraw);
    window.addEventListener('mousemove', moveDraw);
    window.addEventListener('mouseup', endDraw);

    container.addEventListener('touchstart', startDraw, { passive: false });
    window.addEventListener('touchmove', moveDraw, { passive: false });
    window.addEventListener('touchend', endDraw);
  }

  // Setup Pattern Lock on Security Page
  let setupDrawnPattern = '';
  initPatternLock('setupPatternContainer', 'setupPatternSvg', 'setupPatternPolyline', function(pattern, nodes) {
    if (pattern.split('-').length < 4) {
      nodes.forEach(n => n.classList.add('error'));
      $('#setupPatternStatus').html('<span style="color: var(--danger);">Pattern must connect at least 4 dots!</span>');
      setupDrawnPattern = '';
    } else {
      setupDrawnPattern = pattern;
      $('#setupPatternStatus').html('<span style="color: var(--success);">Pattern recorded! Click "Save Pattern" to apply.</span>');
    }
  });

  $('#clearSetupPatternBtn').on('click', function() {
    setupDrawnPattern = '';
    const container = document.getElementById('setupPatternContainer');
    if (container) {
      container.querySelectorAll('.pattern-node').forEach(n => n.classList.remove('selected', 'error'));
      const poly = document.getElementById('setupPatternPolyline');
      if (poly) poly.setAttribute('points', '');
    }
    $('#setupPatternStatus').empty();
  });

  $('#savePatternBtn').on('click', function() {
    if (!setupDrawnPattern) {
      alert('Please draw a pattern with at least 4 dots first.');
      return;
    }

    $.post('index.php?api=security', { action: 'set_pattern', pattern: setupDrawnPattern }, function(res) {
      if (res.success) {
        alert('✅ ' + res.message);
        window.location.reload();
      } else {
        alert('❌ ' + res.message);
      }
    }, 'json');
  });

  // Modal Pattern Lock
  initPatternLock('patternContainer', 'patternSvg', 'patternPolyline', function(pattern, nodes) {
    $('#securityFeedbackMsg').html('<span style="color: var(--secondary);">Verifying Pattern...</span>');
    $.post('index.php?api=security', { action: 'verify_pattern', pattern: pattern }, function(res) {
      if (res.success) {
        $('#securityFeedbackMsg').html('<span style="color: var(--success);">✅ Verified!</span>');
        setTimeout(() => $('#securityLockModal').removeClass('active'), 800);
      } else {
        nodes.forEach(n => n.classList.add('error'));
        $('#securityFeedbackMsg').html('<span style="color: var(--danger);">❌ ' + res.message + '</span>');
      }
    }, 'json');
  });

  // Set PIN Form Submit
  $('#setPinForm').on('submit', function(e) {
    e.preventDefault();
    $.post('index.php?api=security', $(this).serialize() + '&action=set_pin', function(res) {
      if (res.success) {
        alert('✅ ' + res.message);
        window.location.reload();
      } else {
        alert('❌ ' + res.message);
      }
    }, 'json');
  });

  // Security Modal Trigger
  $('#testPinModalBtn').on('click', function() {
    $('#securityLockModal').addClass('active');
  });

  $('#closeSecurityModalBtn').on('click', function() {
    $('#securityLockModal').removeClass('active');
  });

  $('#tabPinBtn').on('click', function() {
    $(this).addClass('active');
    $('#tabPatternBtn').removeClass('active');
    $('#pinLockView').show();
    $('#patternLockView').hide();
  });

  $('#tabPatternBtn').on('click', function() {
    $(this).addClass('active');
    $('#tabPinBtn').removeClass('active');
    $('#patternLockView').show();
    $('#pinLockView').hide();
  });

  // Remove PIN Handler
  $('#removePinBtn').on('click', function() {
    if (confirm('Are you sure you want to remove your 4-Digit Security PIN?')) {
      $.post('index.php?api=security', { action: 'remove_pin' }, function(res) {
        if (res.success) {
          alert('✅ ' + res.message);
          window.location.reload();
        } else {
          alert('❌ ' + res.message);
        }
      }, 'json');
    }
  });

  // Remove Pattern Handler
  $('#removePatternBtn').on('click', function() {
    if (confirm('Are you sure you want to remove your 3x3 Pattern Lock?')) {
      $.post('index.php?api=security', { action: 'remove_pattern' }, function(res) {
        if (res.success) {
          alert('✅ ' + res.message);
          window.location.reload();
        } else {
          alert('❌ ' + res.message);
        }
      }, 'json');
    }
  });
});
