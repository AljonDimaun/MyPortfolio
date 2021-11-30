<?php

	WS_Form_Common::loader();

	WS_Form_Common::option_set('intro', true);
?>
<!-- Welcome Banner -->
<div id="wsf-welcome">

<!-- Slide 1 - Welcome -->
<div class="wsf-welcome-slide" data-id="1">

<div class="wsf-welcome-copy">
<div class="wsf-welcome-logo"><svg xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 503.2 206.6" xml:space="preserve"><title><?php esc_html_e(sprintf(__('%s - Build Better WordPress Forms', 'ws-form'), WS_FORM_NAME_PRESENTABLE)); ?></title><path d="M75.3 148L59.8 78.6l-.4-1.6-3.3-18.1H56l-1.4 8-2.4 11.7-16 69.4H24.4L0 45.5h9.8l14 59.7 6.5 31.2h.6a330 330 0 016-31.4l14-59.5h10.3l14.1 59.7c1.1 4.5 3.1 14.9 5.9 31.2h.6c.2-2.1 1.2-7.3 3.1-15.7 1.8-8.4 7.7-33.4 17.5-75.2h9.7L86.9 148H75.3zM173.2 122.3c0 8.6-2.5 15.4-7.4 20.3-5 4.9-12.1 7.3-21.5 7.3-5.1 0-9.6-.6-13.4-1.8a32 32 0 01-9-4l4.3-7.5c2.9 1.8 2.1 1.3 5.8 2.6a38 38 0 0012.9 2.1 18 18 0 0013.7-5.2c3.3-3.5 5-8.1 5-13.9 0-4.5-1.2-8.4-3.6-11.5a48.2 48.2 0 00-13.6-10.6 86.5 86.5 0 01-15.7-10.6 26.6 26.6 0 01-8.8-20.6c0-7.4 2.7-13.5 8.2-18.3a30.3 30.3 0 0120.8-7.2c9 0 15.8 2.3 21.9 6.2l-4.3 7.5a33.8 33.8 0 00-18-5.2c-5.8 0-10.4 1.6-13.9 4.7a16 16 0 00-5.2 12.3c0 4.5 1.2 8.3 3.5 11.4 2.3 3.1 7.3 6.8 14.9 11.1 7.4 4.5 12.5 8 15.3 10.7 2.8 2.7 4.8 5.6 6.2 8.9 1.3 3.4 1.9 7.1 1.9 11.3zM225.3 53.5h-17.6V148H198V53.5h-14.3l.1-7.8h14.1l.1-8.9c0-13 1.2-21.3 4.7-27 3.6-6 9.6-9.7 18-9.7h10.1v8.3l-9.6.1c-4.9.1-7 1.6-8.8 3.4-1.7 1.8-2.6 4-3.5 8.1-.8 4.1-1.2 9.8-1.2 17v8.6h17.6v7.9zM300 96.5c0 17.3-3 30.5-8.9 39.6a28.7 28.7 0 01-25.6 13.7c-11 0-19.4-4.6-25.2-13.7-5.8-9.2-8.7-22.4-8.7-39.6 0-35.3 11.4-53 34.3-53 10.8 0 19.1 4.6 25.1 13.9s9 22.3 9 39.1zm-58.3 0c0 14.8 1.9 26 5.8 33.5a19 19 0 0018.1 11.3c16.1 0 24.1-15 24.1-44.9 0-29.7-8-44.5-24.1-44.5-8.4 0-14.5 3.7-18.3 11.1a78.8 78.8 0 00-5.6 33.5zM315.6 68.8c0-12.4 15-25.1 31-25.2 10.8-.1 14.7 3 18.6 4.8l-4.9 7.5a28 28 0 00-13.9-3.5c-4.7-.1-8.4.5-12.6 3.2-3.4 2.3-7.1 4.5-8.3 14.3-.8 6.6-.3 16.1-.3 23.7V148h-9.8M366.6 67c1.9-16.9 17.4-23.5 28.2-23.5a33 33 0 0117.6 5.1c3.3 2.5 5.1 4.7 7.1 11.3 2.7-6.3 4.9-8.2 8.8-11a26 26 0 0115.3-5.2c8.5 0 16.9 2.6 22 10 4.1 5.9 5.9 14.3 5.9 27.4v67h-9.7V78.2c.2-19.9-5-26.3-18.2-26.3-6.5 0-11.3 1.6-14.8 7.7-3.4 6-5 16.8-5 28.5v60h-9.7V78.2c0-8.7-1.3-15.2-4-19.4s-9.3-6.5-15-6.5c-7.4 0-12.5 3.6-15.8 9.8-3.4 6.2-3 15.8-3 29.6V148h-9.8"/><circle cx="494.4" cy="52.3" r="8.8"/><circle cx="494.4" cy="95.5" r="8.8"/><circle cx="494.4" cy="138.2" r="8.8"/><path d="M4 203.2V176h7.8c1.6 0 2.9.2 4 .5s2.1.8 2.8 1.4 1.3 1.3 1.7 2.2c.4.9.5 1.8.5 2.9 0 .7-.1 1.4-.4 2.1-.2.7-.6 1.3-1.1 1.8a8 8 0 01-1.7 1.5l-2.4 1c2.1.3 3.8 1 4.9 2.1a5.7 5.7 0 011.7 4.4c0 1.2-.2 2.2-.6 3.1s-1 1.7-1.8 2.3c-.8.6-1.7 1.1-2.9 1.5-1.1.3-2.4.5-3.9.5H4zm2-14.4h5.9c1.3 0 2.3-.2 3.2-.5.9-.3 1.6-.7 2.2-1.3.6-.5 1-1.1 1.3-1.8.3-.7.4-1.4.4-2.1 0-1.8-.6-3.2-1.7-4.2a9 9 0 00-5.4-1.4H6v11.3zm0 1.4v11.5h6.8c2.4 0 4.2-.5 5.4-1.5 1.2-1 1.8-2.5 1.8-4.4 0-.9-.2-1.6-.5-2.3-.3-.7-.8-1.3-1.4-1.8s-1.4-.8-2.3-1.1c-.9-.3-1.9-.4-3.1-.4H6zM29.1 184.1v12.2c0 1.8.4 3.2 1.2 4.2.8 1 2.1 1.5 3.8 1.5 1.2 0 2.4-.3 3.5-1a9 9 0 002.9-2.6V184h1.8v19.1h-1c-.4 0-.5-.2-.6-.5l-.2-2.8c-.9 1.1-1.9 2-3.1 2.7-1.2.7-2.5 1-3.9 1-1.1 0-2-.2-2.8-.5s-1.5-.8-2-1.4c-.5-.6-.9-1.4-1.2-2.3-.3-.9-.4-1.9-.4-3v-12.2h2zM51.8 177.5l-.1.6-.4.5-.5.4-.7.1-.7-.1-.5-.4-.4-.5-.1-.6.1-.7.4-.5.5-.4.7-.1.7.1c.3.1.4.2.5.4l.4.5.1.7zm-.8 6.6v19.1h-1.8v-19.1H51zM60.1 175.3v28h-1.8v-28h1.8zM80.5 203.2c-.3 0-.5-.2-.6-.5l-.2-3c-.8 1.2-1.8 2.1-3 2.8s-2.4 1-3.8 1c-2.4 0-4.2-.8-5.5-2.4-1.3-1.6-2-4.1-2-7.3 0-1.4.2-2.7.5-3.9s.9-2.3 1.6-3.2a7.5 7.5 0 016.2-2.9c1.3 0 2.4.2 3.4.7 1 .5 1.8 1.2 2.5 2.1v-11.4h1.8v28h-.9zm-7.1-1.2c1.3 0 2.4-.3 3.4-1s1.9-1.6 2.8-2.7v-10.1a6 6 0 00-2.5-2.3 7.3 7.3 0 00-6-.1c-.8.4-1.6 1-2.1 1.7s-1 1.6-1.3 2.7c-.3 1-.4 2.2-.4 3.5 0 2.8.5 4.9 1.6 6.3s2.6 2 4.5 2zM96.2 203.2V176h7.8c1.6 0 2.9.2 4 .5s2.1.8 2.8 1.4 1.3 1.3 1.7 2.2c.4.9.5 1.8.5 2.9 0 .7-.1 1.4-.4 2.1-.2.7-.6 1.3-1.1 1.8a8 8 0 01-1.7 1.5l-2.4 1c2.1.3 3.8 1 4.9 2.1a5.7 5.7 0 011.7 4.4c0 1.2-.2 2.2-.6 3.1s-1 1.7-1.8 2.3c-.8.6-1.7 1.1-2.9 1.5-1.1.3-2.4.5-3.9.5h-8.6zm2-14.4h5.9c1.3 0 2.3-.2 3.2-.5.9-.3 1.6-.7 2.2-1.3.6-.5 1-1.1 1.3-1.8.3-.7.4-1.4.4-2.1 0-1.8-.6-3.2-1.7-4.2a9 9 0 00-5.4-1.4h-5.9v11.3zm0 1.4v11.5h6.8c2.4 0 4.2-.5 5.4-1.5 1.2-1 1.8-2.5 1.8-4.4 0-.9-.2-1.6-.5-2.3-.3-.7-.8-1.3-1.4-1.8s-1.4-.8-2.3-1.1c-.9-.3-1.9-.4-3.1-.4h-6.7zM127.2 183.8a7.8 7.8 0 015.4 2.2c.7.7 1.2 1.6 1.6 2.6.4 1 .6 2.2.6 3.6l-.1.6-.4.2h-13.8v.4c0 1.4.2 2.7.5 3.8a7 7 0 003.6 4.4c.9.4 1.8.6 2.9.6a9 9 0 004.3-1l1.1-.7.6-.3.4.2.5.6-1.2 1.1-1.7.9-2 .6c-.7.2-1.4.2-2.1.2a8.6 8.6 0 01-6.4-2.7 8.3 8.3 0 01-1.8-3.2 14.4 14.4 0 010-8.1c.4-1.2 1-2.2 1.7-3a8 8 0 012.7-2c1-.8 2.2-1 3.6-1zm0 1.4c-1 0-1.9.2-2.7.5-.8.3-1.5.8-2 1.3a7.7 7.7 0 00-2.1 4.8H133c0-1-.1-1.9-.4-2.8s-.7-1.5-1.2-2.1c-.5-.6-1.1-1-1.8-1.3a6 6 0 00-2.4-.4zM144.9 203.6a4 4 0 01-3-1.1c-.7-.7-1.1-1.8-1.1-3.3v-13.1H138l-.3-.1-.1-.3v-.7l3.3-.2.5-6.9.1-.3.3-.1h.9v7.3h6v1.4h-6v13l.2 1.4c.1.4.3.7.6.9l.8.5 1 .2 1.2-.2.9-.4.6-.4.4-.2.3.2.5.8c-.5.5-1.1 1-1.9 1.3s-1.6.3-2.4.3zM158.6 203.6a4 4 0 01-3-1.1c-.7-.7-1.1-1.8-1.1-3.3v-13.1h-2.8l-.3-.1-.1-.3v-.7l3.3-.2.5-6.9.1-.3.3-.1h.9v7.3h6v1.4h-6v13l.2 1.4c.1.4.3.7.6.9l.8.5 1 .2 1.2-.2.9-.4.6-.4.4-.2.3.2.5.8c-.5.5-1.1 1-1.9 1.3s-1.6.3-2.4.3zM174.3 183.8a7.8 7.8 0 015.4 2.2c.7.7 1.2 1.6 1.6 2.6.4 1 .6 2.2.6 3.6l-.1.6-.4.2h-13.8v.4c0 1.4.2 2.7.5 3.8a7 7 0 003.6 4.4c.9.4 1.8.6 2.9.6a9 9 0 004.3-1l1.1-.7.6-.3.4.2.5.6-1.2 1.1-1.7.9-2 .6c-.7.2-1.4.2-2.1.2a8.6 8.6 0 01-6.4-2.7 8.3 8.3 0 01-1.8-3.2 14.4 14.4 0 010-8.1c.4-1.2 1-2.2 1.7-3a8 8 0 012.7-2c1-.8 2.3-1 3.6-1zm0 1.4c-1 0-1.9.2-2.7.5-.8.3-1.5.8-2 1.3a7.7 7.7 0 00-2.1 4.8h12.6c0-1-.1-1.9-.4-2.8s-.7-1.5-1.2-2.1c-.5-.6-1.1-1-1.8-1.3a6 6 0 00-2.4-.4zM186.8 203.2v-19.1h1l.5.1c.1.1.2.2.2.5l.2 4a7.5 7.5 0 012.5-3.6c1-.9 2.3-1.3 3.8-1.3l1.6.2 1.4.5-.2 1.3c0 .2-.2.3-.4.3l-.3-.1-.5-.2-.8-.2-1.1-.1c-1.4 0-2.6.4-3.6 1.3-.9.9-1.7 2.2-2.3 3.9v12.4h-2zM206.7 176h1.6c.4 0 .6.2.7.5l6.9 22.4.2.8.2.9.2-.9.2-.8 7.7-22.4.3-.4.5-.2h.5l.4.1.3.4 7.7 22.4.2.8.2.9.2-.9.2-.8 6.9-22.4.3-.4.5-.2h1.5l-8.6 27.2h-1.8l-8-23.6-.3-1-.3 1L217 203h-1.8l-8.5-27zM254.4 183.8c1.4 0 2.6.2 3.7.7 1.1.5 2 1.1 2.7 2 .7.9 1.3 1.9 1.7 3.1.4 1.2.6 2.6.6 4.1a13 13 0 01-.6 4.1c-.4 1.2-1 2.2-1.7 3.1s-1.7 1.5-2.7 2c-1.1.5-2.3.7-3.7.7-1.4 0-2.6-.2-3.7-.7-1.1-.5-2-1.1-2.8-2s-1.3-1.9-1.7-3.1a13 13 0 01-.6-4.1c0-1.5.2-2.9.6-4.1.4-1.2 1-2.2 1.7-3.1a8 8 0 012.8-2c1.1-.5 2.3-.7 3.7-.7zm0 18.3c1.1 0 2.1-.2 3-.6s1.6-1 2.1-1.7c.6-.7 1-1.6 1.3-2.6s.4-2.2.4-3.5c0-1.3-.1-2.4-.4-3.5s-.7-1.9-1.3-2.7-1.3-1.3-2.1-1.7c-.9-.4-1.9-.6-3-.6s-2.1.2-3 .6c-.9.4-1.6 1-2.1 1.7-.6.7-1 1.6-1.3 2.7s-.4 2.2-.4 3.5c0 1.3.1 2.4.4 3.5s.7 1.9 1.3 2.6c.6.7 1.3 1.3 2.1 1.7.8.4 1.8.6 3 .6zM267.9 203.2v-19.1h1l.5.1c.1.1.2.2.2.5l.2 4a7.5 7.5 0 012.5-3.6c1-.9 2.3-1.3 3.8-1.3l1.6.2 1.4.5-.2 1.3c0 .2-.2.3-.4.3l-.3-.1-.5-.2-.8-.2-1.1-.1c-1.4 0-2.6.4-3.6 1.3-.9.9-1.7 2.2-2.3 3.9v12.4h-2zM296.7 203.2c-.3 0-.5-.2-.6-.5l-.2-3c-.8 1.2-1.8 2.1-3 2.8s-2.4 1-3.8 1c-2.4 0-4.2-.8-5.5-2.4-1.3-1.6-2-4.1-2-7.3 0-1.4.2-2.7.5-3.9s.9-2.3 1.6-3.2a7.5 7.5 0 016.2-2.9c1.3 0 2.4.2 3.4.7 1 .5 1.8 1.2 2.5 2.1v-11.4h1.8v28h-.9zm-7-1.2c1.3 0 2.4-.3 3.5-1 1-.7 1.9-1.6 2.8-2.7v-10.1a6 6 0 00-2.5-2.3 7.3 7.3 0 00-6-.1c-.8.4-1.6 1-2.1 1.7-.6.8-1 1.6-1.3 2.7-.3 1-.4 2.2-.4 3.5 0 2.8.5 4.9 1.6 6.3.9 1.3 2.5 2 4.4 2zM307.6 192.3v11h-2V176h7c3.2 0 5.6.7 7.2 2.1 1.6 1.4 2.4 3.4 2.4 6 0 1.2-.2 2.3-.7 3.3-.4 1-1.1 1.9-1.9 2.6s-1.8 1.3-3 1.7c-1.2.4-2.5.6-4 .6h-5zm0-1.6h5.1c1.2 0 2.3-.2 3.2-.5.9-.3 1.8-.8 2.4-1.4.7-.6 1.2-1.3 1.5-2.1.4-.8.5-1.7.5-2.6 0-2.1-.6-3.7-1.9-4.8-1.3-1.1-3.2-1.7-5.8-1.7h-5.1v13.1zM327 203.2v-19.1h1l.5.1c.1.1.2.2.2.5l.2 4a7.5 7.5 0 012.5-3.6c1-.9 2.3-1.3 3.8-1.3l1.6.2 1.4.5-.2 1.3c0 .2-.2.3-.4.3l-.3-.1-.5-.2-.8-.2-1.1-.1c-1.4 0-2.6.4-3.6 1.3-.9.9-1.7 2.2-2.3 3.9v12.4h-2zM349.5 183.8a7.8 7.8 0 015.4 2.2c.7.7 1.2 1.6 1.6 2.6.4 1 .6 2.2.6 3.6l-.1.6-.4.2h-13.8v.4c0 1.4.2 2.7.5 3.8a7 7 0 003.6 4.4c.9.4 1.8.6 2.9.6a9 9 0 004.3-1l1.1-.7.6-.3.4.2.5.6-1.2 1.1c-.5.4-1.1.6-1.7.9l-2 .6c-.7.2-1.4.2-2.1.2a8.6 8.6 0 01-6.4-2.7 8.3 8.3 0 01-1.8-3.2 14.4 14.4 0 010-8.1c.4-1.2 1-2.2 1.7-3 .7-.8 1.6-1.5 2.7-2s2.3-1 3.6-1zm0 1.4c-1 0-1.9.2-2.7.5-.8.3-1.5.8-2 1.3s-1 1.3-1.4 2.1-.6 1.7-.7 2.7h12.6c0-1-.1-1.9-.4-2.8s-.7-1.5-1.2-2.1c-.5-.6-1.1-1-1.8-1.3a6 6 0 00-2.4-.4zM372.5 186.5c-.1.2-.2.3-.4.3l-.5-.2-.9-.5-1.3-.5c-.5-.2-1.2-.2-2-.2a5.3 5.3 0 00-3.4 1.1c-.4.3-.7.7-.9 1.2s-.3.9-.3 1.4c0 .6.2 1.1.5 1.5.3.4.7.7 1.2 1l1.7.7 2 .6 2 .7c.6.2 1.2.6 1.7.9.5.4.9.8 1.2 1.4.3.5.5 1.2.5 2a6 6 0 01-1.8 4.2c-.6.5-1.3.9-2.1 1.3a9.5 9.5 0 01-6.3-.1 9 9 0 01-2.6-1.7l.4-.7.2-.2.3-.1.6.3 1 .7 1.5.7c.6.2 1.3.3 2.2.3.8 0 1.5-.1 2.1-.3a3.8 3.8 0 002.4-2.3 5 5 0 00.3-1.6c0-.6-.2-1.2-.5-1.6s-.7-.8-1.2-1.1l-1.7-.8-2-.6-2-.7c-.6-.2-1.2-.6-1.7-.9a4.2 4.2 0 01-1.7-3.4c0-.7.1-1.3.4-2 .3-.6.7-1.2 1.3-1.7s1.2-.9 2-1.1 1.7-.4 2.6-.4c1.2 0 2.2.2 3.1.5.9.3 1.7.9 2.5 1.6l-.4.3zM389.1 186.5c-.1.2-.2.3-.4.3l-.5-.2-.9-.5-1.3-.5c-.5-.2-1.2-.2-2-.2a5.3 5.3 0 00-3.4 1.1c-.4.3-.7.7-.9 1.2s-.3.9-.3 1.4c0 .6.2 1.1.5 1.5.3.4.7.7 1.2 1l1.7.7 2 .6 2 .7c.6.2 1.2.6 1.7.9.5.4.9.8 1.2 1.4.3.5.5 1.2.5 2a6 6 0 01-1.8 4.2c-.6.5-1.3.9-2.1 1.3a9.5 9.5 0 01-6.3-.1 9 9 0 01-2.6-1.7l.4-.7.2-.2.3-.1.6.3 1 .7 1.5.7c.6.2 1.3.3 2.2.3.8 0 1.5-.1 2.1-.3a3.8 3.8 0 002.4-2.3 5 5 0 00.3-1.6c0-.6-.2-1.2-.5-1.6s-.7-.8-1.2-1.1l-1.7-.8-2-.6-2-.7c-.6-.2-1.2-.6-1.7-.9a4.2 4.2 0 01-1.7-3.4c0-.7.1-1.3.4-2 .3-.6.7-1.2 1.3-1.7s1.2-.9 2-1.1 1.7-.4 2.6-.4c1.2 0 2.2.2 3.1.5.9.3 1.7.9 2.5 1.6l-.4.3zM419.7 176v1.6h-14.3V189h12.4v1.6h-12.4v12.5h-2V176h16.3zM430.3 183.8c1.4 0 2.6.2 3.7.7 1.1.5 2 1.1 2.7 2 .7.9 1.3 1.9 1.7 3.1.4 1.2.6 2.6.6 4.1a13 13 0 01-.6 4.1c-.4 1.2-1 2.2-1.7 3.1s-1.7 1.5-2.7 2c-1.1.5-2.3.7-3.7.7-1.4 0-2.6-.2-3.7-.7a8 8 0 01-2.8-2 8.5 8.5 0 01-1.7-3.1 13 13 0 01-.6-4.1c0-1.5.2-2.9.6-4.1.4-1.2 1-2.2 1.7-3.1a8 8 0 012.8-2 9 9 0 013.7-.7zm0 18.3c1.1 0 2.1-.2 3-.6.9-.4 1.6-1 2.1-1.7a7 7 0 001.3-2.6c.3-1 .4-2.2.4-3.5a13 13 0 00-.4-3.5c-.3-1-.7-1.9-1.3-2.7a6.8 6.8 0 00-2.1-1.7c-.9-.4-1.9-.6-3-.6s-2.1.2-3 .6c-.9.4-1.6 1-2.1 1.7-.6.7-1 1.6-1.3 2.7-.3 1-.4 2.2-.4 3.5 0 1.3.1 2.4.4 3.5a7 7 0 001.3 2.6c.6.7 1.3 1.3 2.1 1.7.9.4 1.9.6 3 .6zM443.9 203.2v-19.1h1l.5.1c.1.1.2.2.2.5l.2 4a7.5 7.5 0 012.5-3.6c1-.9 2.3-1.3 3.8-1.3l1.6.2 1.4.5-.2 1.3c0 .2-.2.3-.4.3l-.3-.1-.5-.2-.8-.2-1.1-.1c-1.4 0-2.6.4-3.6 1.3-.9.9-1.7 2.2-2.3 3.9v12.4h-2zM459.2 203.2v-19.1h1c.3 0 .5.2.6.5l.2 2.8 1.2-1.4a7.6 7.6 0 012.9-1.8c.5-.2 1.1-.3 1.7-.3 1.4 0 2.5.4 3.3 1.2.8.8 1.4 1.9 1.7 3.4.2-.8.6-1.5 1-2.1.4-.6.9-1.1 1.4-1.4.5-.4 1.1-.7 1.8-.8.6-.2 1.3-.3 1.9-.3.9 0 1.8.2 2.6.5s1.4.8 1.9 1.4.9 1.4 1.2 2.3c.3.9.4 1.9.4 3.1v12.2h-1.8V191c0-1.9-.4-3.3-1.2-4.3s-2-1.5-3.5-1.5a4 4 0 00-1.9.4c-.6.2-1.1.6-1.6 1.1-.5.5-.8 1.1-1.1 1.8-.3.7-.4 1.6-.4 2.5v12.2h-1.8V191c0-1.9-.4-3.3-1.1-4.3s-1.8-1.5-3.3-1.5c-1 0-2 .3-2.9.9-.9.6-1.7 1.5-2.4 2.6v14.5h-1.8zM500.3 186.5c-.1.2-.2.3-.4.3l-.5-.2-.9-.5-1.3-.5c-.5-.2-1.2-.2-2-.2a5.3 5.3 0 00-3.4 1.1c-.4.3-.7.7-.9 1.2s-.3.9-.3 1.4c0 .6.2 1.1.5 1.5.3.4.7.7 1.2 1l1.7.7 2 .6 2 .7c.6.2 1.2.6 1.7.9.5.4.9.8 1.2 1.4.3.5.5 1.2.5 2a6 6 0 01-1.8 4.2c-.6.5-1.3.9-2.1 1.3a9.5 9.5 0 01-6.3-.1 9 9 0 01-2.6-1.7l.4-.7.2-.2.3-.1.6.3 1 .7 1.5.7c.6.2 1.3.3 2.2.3.8 0 1.5-.1 2.1-.3a3.8 3.8 0 002.4-2.3 5 5 0 00.3-1.6c0-.6-.2-1.2-.5-1.6s-.7-.8-1.2-1.1l-1.7-.8-2-.6-2-.7c-.6-.2-1.2-.6-1.7-.9a4.2 4.2 0 01-1.7-3.4c0-.7.1-1.3.4-2 .3-.6.7-1.2 1.3-1.7s1.2-.9 2-1.1 1.7-.4 2.6-.4c1.2 0 2.2.2 3.1.5.9.3 1.7.9 2.5 1.6l-.4.3z"/></svg></div>
<?php

	// Partner
	$partner_logo_text = getenv('wsf_partner_logo_text');
	$partner_logo_url = getenv('wsf_partner_logo_url');
	$partner_logo_width = getenv('wsf_partner_logo_width');
	$partner_logo_height = getenv('wsf_partner_logo_height');
	$partner_logo_alt = getenv('wsf_partner_logo_alt');

	if(
		($partner_logo_text !== false) ||
		($partner_logo_url !== false) 
	) {
?>
<div class="wsf-welcome-partner">
<?php
		if($partner_logo_text !== false) {
?>
<p><?php echo esc_html($partner_logo_text); ?></p>
<?php
		}

		if($partner_logo_url !== false) {
?>
<img src="<?php echo esc_attr($partner_logo_url); ?>"<?php if($partner_logo_width !== false) { ?> width="<?php echo esc_attr($partner_logo_width); ?>" <?php } ?><?php if($partner_logo_height !== false) { ?> height="<?php echo esc_attr($partner_logo_height); ?>" <?php } ?><?php if($partner_logo_alt !== false) { ?> alt="<?php echo esc_attr($partner_logo_alt); ?>" title="<?php echo esc_attr($partner_logo_alt); ?>" <?php } ?> />
<?php
		}
?>
</div>
<?php
		
	}
?>
</div>

<button class="wsf-welcome-button" data-slide-next-id="2"><?php esc_html_e('Click to Start', 'ws-form'); ?></button>

</div>
<!-- /Slide 1 - Welcome -->

<!-- Slide 2 - Basic / Advanced -->
<div class="wsf-welcome-slide" data-id="2">

<div class="wsf-welcome-copy">
<div class="wsf-welcome-title"><?php esc_html_e('How familiar are you with building forms?', 'ws-form') ?></div>
<div class="wsf-welcome-intro"><?php esc_html_e('If you\'re new to building forms, we\'ll keep it simple.', 'ws-form'); ?></div>
</div>

<button class="wsf-welcome-button" data-slide-next-id="5" data-action="wsf-mode-set" data-value="basic"><?php esc_html_e('Keep It Simple', 'ws-form') ?></button>
<button class="wsf-welcome-button" data-slide-next-id="5" data-action="wsf-mode-set" data-value="basic"><?php esc_html_e('I\'m Familiar', 'ws-form') ?></button>
<button class="wsf-welcome-button" data-slide-next-id="4" data-action="wsf-mode-set" data-value="advanced"><?php esc_html_e('I\'m a Developer', 'ws-form') ?></button>

</div>
<!-- /Slide 2 - Basic / Advanced -->

<!-- Slide 3 - Framework Detect -->
<div class="wsf-welcome-slide" data-id="3">

<div class="wsf-welcome-copy">
<div class="wsf-welcome-title"><?php esc_html_e("You're using ", 'ws-form') ?><span id="wsf-welcome-framework"></span></div>
<div class="wsf-welcome-intro"><?php esc_html_e('Is that correct?', 'ws-form'); ?></div>
</div>

<button class="wsf-welcome-button" data-slide-next-id="5" data-action="wsf-framework-set"><?php esc_html_e('Yes'); ?></button>
<button class="wsf-welcome-button" data-slide-next-id="4"><?php esc_html_e('No', 'ws-form'); ?></button>
<button class="wsf-welcome-button" data-slide-next-id="5"><?php esc_html_e('I\'m Not Sure', 'ws-form'); ?></button>

</div>
<!-- /Slide 3 - Framework Detect -->

<!-- Slide 4 - Framework Select-->
<div class="wsf-welcome-slide" data-id="4">

<div class="wsf-welcome-copy">
<div class="wsf-welcome-title"><?php esc_html_e('Does your theme use a front-end framework?', 'ws-form') ?></div>
<div class="wsf-welcome-intro"><?php esc_html_e('Not sure? No problem. You can change this later.', 'ws-form'); ?></div>
</div>

<select id="framework" data-slide-next-id="5" class="wsf-welcome-select">
<option value=""><?php esc_html_e("Select..."); ?></option>
<option value="<?php echo esc_attr(WS_FORM_DEFAULT_FRAMEWORK); ?>"><?php esc_html_e('I\'m Not Sure', 'ws-form'); ?></option>
<option value="<?php echo esc_attr(WS_FORM_DEFAULT_FRAMEWORK); ?>"><?php esc_html_e('I\'m Not Using A Framework', 'ws-form'); ?></option>
<?php

	$frameworks = WS_Form_Config::get_frameworks(false);
	$framework_types = $frameworks['types'];
	foreach($framework_types as $type => $framework) {

		// Skip default framework (ws-form)
		if($type == WS_FORM_DEFAULT_FRAMEWORK) { continue; }

?><option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($framework['name']); ?></option>
<?php

	}

?></select>

<button class="wsf-welcome-button" data-slide-next-id="5"><?php esc_html_e('Skip This', 'ws-form'); ?></button>

</div>
<!-- /Slide 4 - Framework Select -->

<!-- Slide 5 - Setup Complete -->
<div class="wsf-welcome-slide" data-id="5" data-action="wsf-setup-push">

<div class="wsf-welcome-copy">
<div class="wsf-welcome-title"><?php esc_html_e('All Done!', 'ws-form') ?></div>
<div class="wsf-welcome-intro"><?php esc_html_e('You\'re ready to build your first form.', 'ws-form'); ?></div>
</div>

<div class="wsf-container">
<div class="wsf-video-container">
<iframe id="wsf-video-welcome" src="https://player.vimeo.com/video/289590605?api=1" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen allow="autoplay; encrypted-media"></iframe>
</div>
</div>

<script src="https://player.vimeo.com/api/player.js"></script>

<div><button class="wsf-welcome-button" data-action="wsf-form-add"><?php esc_html_e('Get Started...', 'ws-form'); ?></button></div>

</div>
<!-- /Slide 5 - Setup Complete -->

<!-- Slide 6 - API Error -->
<div class="wsf-welcome-slide" data-id="6">

<div class="wsf-welcome-copy">
<div class="wsf-welcome-title"><?php esc_html_e("Whoops! Something went wrong.", 'ws-form') ?></div>
<div class="wsf-welcome-intro"><?php esc_html_e("There appears to be a problem with your hosting. For more information, click the 'Help' button below.", 'ws-form'); ?><span class="wsf-welcome-api-error"></span></div>
</div>

<button class="wsf-welcome-button" data-action="wsf-try-again"><?php esc_html_e('Try Again', 'ws-form'); ?></button>
<a href="<?php echo WS_Form_Common::get_plugin_website_url('/knowledgebase/installation-troubleshooting/'); ?>" target="_blank" class="wsf-welcome-button"><?php esc_html_e('Help', 'ws-form'); ?></a>

</div>
<!-- /Slide 6 - API Error -->

</div>
<!-- /Welcome Banner -->

<script>

	// Options
	var params_setup = {

		'framework': '<?php echo esc_html(WS_FORM_DEFAULT_FRAMEWORK); ?>',
		'mode': '<?php echo esc_html(WS_FORM_DEFAULT_MODE); ?>'
	};

	var framework_detected = false;

	(function($) {

		'use strict';

		// On load
		$(function() {

			var wsf_obj = new $.WS_Form();
			var wsf_welcome_banner = $('#wsf-welcome');

			// Highlight menu
			$('#toplevel_page_ws-form').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu current').addClass('selected');
			$('[href="admin.php?page=ws-form-welcome"]', $('#toplevel_page_ws-form-welcome')).closest('li').addClass('wp-menu-open current');

			// Slide buttons
			$('button.wsf-welcome-button', wsf_welcome_banner).click(function() {

				user_action($(this), $(this).attr('data-value'));
			});

			// Slide select
			$('select', wsf_welcome_banner).change(function() {

				user_action($(this), $(this).val());
			});

			// Framework select
			$('#framework').change(function() {

				params_setup['framework'] = $(this).val();
			})

			// Turn on loader
			wsf_obj.loader_on();

			// Show first slide
			var slide_next = $('.wsf-welcome-slide[data-id="1"]');
			slide_next.fadeIn(200);

			// Detect framework
			wsf_obj.api_test(function() {

				// Detect framework
				wsf_obj.framework_detect(function(framework) {

					if(
						(typeof(framework.name) !== 'undefined') &&
						(framework.name !== false)
					) {

						// Remember framework detected
						framework_detected = framework;

						// Set framework name
						$('#wsf-welcome-framework').html(framework.name);

						// Reconfigure path
						$('.wsf-welcome-slide[data-id="2"] .wsf-welcome-button[data-value="advanced"]').attr('data-slide-next-id', '3');
					}

					// Turn off loader
					wsf_obj.loader_off();

				}, function() {

					// Turn off loader
					wsf_obj.loader_off();
				});

			}, function(error_message) {

				// Set error message
				$('.wsf-welcome-api-error').html((error_message !== false) ? 'Error: ' + error_message : '');

				// API test failed, show error page
				$('.wsf-welcome-slide[data-id="1"]').fadeOut(200, function() {

					// Hide all other slides just in case
					$('.wsf-welcome-slide').hide();

					// Show error slide
					var slide_next = $('.wsf-welcome-slide[data-id="6"]');
					slide_next.fadeIn(200);

					// Turn off loader
					wsf_obj.loader_off();
				});
			});

			function user_action(obj, value) {

				var slide_next_id = obj.attr('data-slide-next-id');

				// Button actions
				var action_button = obj.attr('data-action');
				switch(action_button) {

					// Set framework type
					case 'wsf-framework-set' :

						if(framework_detected !== false) {

							params_setup['framework'] = framework_detected.type;
						}
						break;

					// Set mode
					case 'wsf-mode-set' :

						params_setup['mode'] = value;
						break;

					// Add form
					case 'wsf-form-add' :

						var iframe = $('#wsf-video-welcome');
						var player = new Vimeo.Player(iframe[0]);
						player.pause();
						location.href='<?php echo esc_html(WS_Form_Common::get_admin_url('ws-form-add')); ?>';
						return;

					// Try again
					case 'wsf-try-again' :

						location.href='<?php echo esc_html(WS_Form_Common::get_admin_url('ws-form-welcome')); ?>';
						break;
				}

				var slide_current = obj.closest('.wsf-welcome-slide');

				slide_current.fadeOut(200, function() {

					// Get next slide object
					var slide_next = $('.wsf-welcome-slide[data-id="' + slide_next_id + '"]');

					// Process action
					var action_slide = slide_next.attr('data-action');
					switch(action_slide) {

						case 'wsf-setup-push' :

							// Turn on loader
							wsf_obj.loader_on();

							// Push setup via API
							wsf_obj.setup_push(params_setup, function() {

								// Success
								slide_next.fadeIn(200);

								// Turn off loader
								wsf_obj.loader_off();

							}, function() {

								// Error
								slide_current.fadeIn(200);

								// Turn off loader
								wsf_obj.loader_off();
							});

							break;

						default :

							slide_next.fadeIn(200);
					}
				});
			}
		});

	})(jQuery);

</script>

