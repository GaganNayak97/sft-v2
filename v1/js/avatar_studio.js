/**
 * Advanced Avatar Studio & Interactive Photo Cropper / Filter Engine
 */

$(document).ready(function() {
  const modal = $('#avatarStudioModal');
  const fileInput = $('#customPhotoInput');
  const canvas = document.getElementById('photoCropCanvas');
  const ctx = canvas ? canvas.getContext('2d') : null;

  let loadedImage = null;
  let imgX = 150, imgY = 150;
  let scale = 1.0;
  let rotation = 0; // In degrees: 0, 90, 180, 270
  let isFlipped = false;
  let isDragging = false;
  let startDragX = 0, startDragY = 0;

  // Filter & Adjustments State
  let brightness = 0;   // -50 to 50
  let contrast = 100;   // 50 to 150
  let saturation = 100; // 0 to 200
  let activeFilter = 'normal';

  // Open Studio Modal
  $('#openAvatarStudioBtn, #profileAvatarWrapper').on('click', function() {
    modal.addClass('active');
  });

  $('#closeAvatarModalBtn').on('click', function() {
    modal.removeClass('active');
  });

  modal.on('click', function(e) {
    if ($(e.target).is('#avatarStudioModal')) {
      modal.removeClass('active');
    }
  });

  // Switch between System Avatars and Custom Photo Studio Tabs
  $('#tabSystemAvatars').on('click', function() {
    $(this).addClass('active');
    $('#tabCustomPhoto').removeClass('active');
    $('#systemAvatarsView').show();
    $('#customPhotoStudioView').hide();
  });

  $('#tabCustomPhoto').on('click', function() {
    $(this).addClass('active');
    $('#tabSystemAvatars').removeClass('active');
    $('#systemAvatarsView').hide();
    $('#customPhotoStudioView').show();
  });

  // 1. System Avatar Selection
  let selectedSystemAvatarSvg = null;

  $('.system-avatar-card').on('click', function() {
    $('.system-avatar-card').removeClass('selected');
    $(this).addClass('selected');
    selectedSystemAvatarSvg = $(this).find('.avatar-svg-holder').html().trim();
    $('#applySystemAvatarBtn').prop('disabled', false).text('Apply ' + $(this).data('name'));
  });

  $('#applySystemAvatarBtn').on('click', function() {
    if (!selectedSystemAvatarSvg) return;
    const btn = $(this);
    btn.prop('disabled', true).text('Applying...');

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: { action: 'update_avatar', avatar: selectedSystemAvatarSvg },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          updateLiveAvatarElements(selectedSystemAvatarSvg, true);
          alert('✅ Avatar updated successfully!');
          modal.removeClass('active');
        } else {
          alert('❌ ' + (res.message || 'Failed to update avatar'));
        }
        btn.prop('disabled', false).text('Apply Avatar');
      },
      error: function() {
        alert('Server error while saving avatar.');
        btn.prop('disabled', false).text('Apply Avatar');
      }
    });
  });

  // Reset to Default Initial Avatar
  $('#resetDefaultAvatarBtn').on('click', function() {
    if (!confirm('Reset your profile avatar to the default initial letter?')) return;
    $.post('index.php?api=auth', { action: 'reset_avatar' }, function(res) {
      if (res.success) {
        window.location.reload();
      }
    }, 'json');
  });

  // 2. Custom Photo Upload & Studio
  fileInput.on('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.match('image.*')) {
      alert('Please select a valid image file (PNG, JPG, WebP).');
      return;
    }

    const reader = new FileReader();
    reader.onload = function(evt) {
      const img = new Image();
      img.onload = function() {
        loadedImage = img;
        resetStudioSettings();
        $('#cropWorkspace').show();
        $('#photoDropzone').hide();
        drawCanvas();
      };
      img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
  });

  $('#changePhotoBtn').on('click', function() {
    fileInput.click();
  });

  function resetStudioSettings() {
    imgX = 150;
    imgY = 150;
    scale = 1.0;
    rotation = 0;
    isFlipped = false;
    brightness = 0;
    contrast = 100;
    saturation = 100;
    activeFilter = 'normal';

    $('#zoomSlider').val(1.0);
    $('#brightnessSlider').val(0);
    $('#contrastSlider').val(100);
    $('#saturationSlider').val(100);
    $('.filter-pill').removeClass('active');
    $('.filter-pill[data-filter="normal"]').addClass('active');
  }

  // Canvas Drawing & Filtering Pipeline
  function drawCanvas() {
    if (!ctx || !loadedImage) return;

    const w = canvas.width;
    const h = canvas.height;
    ctx.clearRect(0, 0, w, h);

    // Save context state
    ctx.save();

    // Move to center of canvas
    ctx.translate(imgX, imgY);

    // Rotation
    ctx.rotate((rotation * Math.PI) / 180);

    // Horizontal Flip
    ctx.scale(isFlipped ? -1 : 1, 1);

    // Zoom scale
    ctx.scale(scale, scale);

    // Build CSS Filter string
    let filterString = `brightness(${100 + brightness}%) contrast(${contrast}%) saturate(${saturation}%)`;
    if (activeFilter === 'bw') {
      filterString += ' grayscale(100%)';
    } else if (activeFilter === 'warm') {
      filterString += ' sepia(35%) hue-rotate(-15deg)';
    } else if (activeFilter === 'cool') {
      filterString += ' hue-rotate(180deg) saturate(90%)';
    } else if (activeFilter === 'vibrant') {
      filterString += ' saturate(160%) contrast(115%)';
    } else if (activeFilter === 'vintage') {
      filterString += ' sepia(50%) contrast(90%) brightness(95%)';
    }

    ctx.filter = filterString;

    // Draw the image centered
    const iw = loadedImage.width;
    const ih = loadedImage.height;
    ctx.drawImage(loadedImage, -iw / 2, -ih / 2);

    ctx.restore();

    // Update real-time previews
    updateLivePreviews();
  }

  // Interactive Pan / Drag on Canvas
  if (canvas) {
    function startDrag(e) {
      if (!loadedImage) return;
      isDragging = true;
      const rect = canvas.getBoundingClientRect();
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;
      startDragX = clientX - rect.left - imgX;
      startDragY = clientY - rect.top - imgY;
    }

    function moveDrag(e) {
      if (!isDragging || !loadedImage) return;
      e.preventDefault();
      const rect = canvas.getBoundingClientRect();
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;
      imgX = clientX - rect.left - startDragX;
      imgY = clientY - rect.top - startDragY;
      drawCanvas();
    }

    function endDrag() {
      isDragging = false;
    }

    canvas.addEventListener('mousedown', startDrag);
    window.addEventListener('mousemove', moveDrag);
    window.addEventListener('mouseup', endDrag);

    canvas.addEventListener('touchstart', startDrag, { passive: false });
    window.addEventListener('touchmove', moveDrag, { passive: false });
    window.addEventListener('touchend', endDrag);
  }

  // Zoom Slider
  $('#zoomSlider').on('input', function() {
    scale = parseFloat($(this).val());
    drawCanvas();
  });

  // Rotation Controls
  $('#rotateLeftBtn').on('click', function() {
    rotation = (rotation - 90 + 360) % 360;
    drawCanvas();
  });

  $('#rotateRightBtn').on('click', function() {
    rotation = (rotation + 90) % 360;
    drawCanvas();
  });

  $('#flipHorizontalBtn').on('click', function() {
    isFlipped = !isFlipped;
    drawCanvas();
  });

  // Photo Adjustments Sliders
  $('#brightnessSlider').on('input', function() {
    brightness = parseInt($(this).val());
    drawCanvas();
  });

  $('#contrastSlider').on('input', function() {
    contrast = parseInt($(this).val());
    drawCanvas();
  });

  $('#saturationSlider').on('input', function() {
    saturation = parseInt($(this).val());
    drawCanvas();
  });

  // 1-Click Filters
  $('.filter-pill').on('click', function() {
    $('.filter-pill').removeClass('active');
    $(this).addClass('active');
    activeFilter = $(this).data('filter');
    drawCanvas();
  });

  // Real-Time Previews (Circular)
  function updateLivePreviews() {
    if (!canvas || !loadedImage) return;

    // Export a 200x200 circular crop preview
    const previewCanvas = document.createElement('canvas');
    previewCanvas.width = 200;
    previewCanvas.height = 200;
    const pctx = previewCanvas.getContext('2d');

    // Circular clip
    pctx.beginPath();
    pctx.arc(100, 100, 95, 0, Math.PI * 2);
    pctx.closePath();
    pctx.clip();

    // Draw from source canvas central 200x200 circle
    pctx.drawImage(canvas, 50, 50, 200, 200, 0, 0, 200, 200);

    const dataUrl = previewCanvas.toDataURL('image/webp', 0.9);
    $('#largeAvatarPreview').attr('src', dataUrl);
    $('#navAvatarPreview').attr('src', dataUrl);
  }

  // Save Final Edited Photo
  $('#saveCustomPhotoBtn').on('click', function() {
    if (!loadedImage) return;

    const btn = $(this);
    btn.prop('disabled', true).text('Saving Photo...');

    // Generate clean 256x256 circular avatar
    const exportCanvas = document.createElement('canvas');
    exportCanvas.width = 256;
    exportCanvas.height = 256;
    const ectx = exportCanvas.getContext('2d');

    ectx.beginPath();
    ectx.arc(128, 128, 128, 0, Math.PI * 2);
    ectx.closePath();
    ectx.clip();

    // Sample from canvas center (50, 50, 200, 200) to 256x256
    ectx.drawImage(canvas, 50, 50, 200, 200, 0, 0, 256, 256);

    const finalDataUri = exportCanvas.toDataURL('image/jpeg', 0.88);

    $.ajax({
      url: 'index.php?api=auth',
      type: 'POST',
      data: { action: 'update_avatar', avatar: finalDataUri },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          updateLiveAvatarElements(finalDataUri, false);
          alert('✅ Custom profile photo cropped, filtered, and saved successfully!');
          modal.removeClass('active');
        } else {
          alert('❌ ' + (res.message || 'Failed to save photo'));
        }
        btn.prop('disabled', false).text('Save & Set Profile Photo');
      },
      error: function() {
        alert('Server error while saving profile photo.');
        btn.prop('disabled', false).text('Save & Set Profile Photo');
      }
    });
  });

  // Helper to instantly update DOM elements without full reload
  function updateLiveAvatarElements(avatarContent, isSvg) {
    if (isSvg) {
      $('#navUserAvatar').css({ 'overflow': 'hidden', 'padding': '0', 'background': 'transparent' }).html(avatarContent);
      $('#profileMainAvatarBox').html(avatarContent);
    } else {
      const imgHtml = `<img src="${avatarContent}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
      $('#navUserAvatar').css({ 'overflow': 'hidden', 'padding': '0', 'background': 'transparent' }).html(imgHtml);
      $('#profileMainAvatarBox').html(imgHtml);
    }
  }
});
