    </div> <!-- .app-body -->
  </div> <!-- .app-wrapper -->

  <!-- Chatbot Widget Section -->
  <?php section('chatbot_widget'); ?>

  <!-- Reusable Pattern / PIN Modal -->
  <?php section('pattern_pin_lock'); ?>

  <!-- Scripts -->
  <script src="<?php echo asset('js/pattern_lock.js'); ?>"></script>
  <script src="<?php echo asset('js/search.js'); ?>"></script>
  <script src="<?php echo asset('js/chatbot.js'); ?>"></script>
  <script src="<?php echo asset('js/avatar_studio.js'); ?>"></script>
  <script src="<?php echo asset('js/app.js'); ?>"></script>
</body>
</html>
