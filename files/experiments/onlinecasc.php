<!DOCTYPE html>
<html>
    <head>
        <title>Online CASC test</title>
        <style type='text/css'> 
            #log{
                font-family: Consolas;
                white-space: pre-wrap;
            }
        </style>

        <!-- <script src="/js/Buffer.js?v=<?=filemtime("../../js/Buffer.js")?>"></script> -->
        <script src="/js/bufo.js?v=<?=filemtime("../../js/bufo.js")?>"></script>
        <!-- <script src="/js/blte.js?v=<?=filemtime("../../js/blte.js")?>"></script> -->
        <!-- <script src="/js/js-blp.js?v=<?=filemtime("../../js/js-blp.js")?>"></script> -->
    </head>
    <body>
        <a href='#' id='openDirButton'>Open directory</a>
        <a href='#' id='openFileButton' onClick='openFileFD()'>Open file</a>
        <canvas id='output'></canvas>
        <div id='log'></div>
        <script src="/js/bundle.js?v=<?=filemtime("../../js/bundle.js")?>"></script>

    </body>
</html>