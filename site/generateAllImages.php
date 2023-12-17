<?php
// Start a session
session_start();
$pageKey = $_REQUEST['pageKey'];
;
// Access the JSON object from the session
$geojson = $_SESSION[$pageKey];
//var_dump($geojson);
function removeOldHashDirs($pathpfx)
{
    // Clear out any old reconstructions first
    $topdirs = scandir($pathpfx);
    $hashlength = 32; // 32 chars in a hash filename
    foreach ($topdirs as $topdir) {
        $path = "$pathpfx/$topdir";
        if (!is_dir($path) || $topdir[0] == '.') {
            continue;
        }
        if (strlen($topdir) == $hashlength) {
            // Check how old it is, delete if older than 15 days
            // You need the /. on the end since the hash is a dir
            if (time() - filemtime("$path/.") > 15 * 24 * 3600) {
                system("rm -rf $path");
            }
        } else {
            // Another dir like scotese, go check inside that
            removeOldHashDirs($path);
        }
    }
}

removeOldHashDirs("./pygplates/livedata");
$pageKey = session_id() . '_' . uniqid();
$models = ["Default", "Marcilly", "Scotese"];
?>

<!DOCTYPE html>
<html>
<title>All Reconstruction Models</title>

<head>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        /*
        * rwdImageMaps jQuery plugin v1.6
        *
        * Allows image maps to be used in a responsive design by recalculating the area coordinates to match the actual image size on load and window.resize
        *
        * Copyright (c) 2016 Matt Stow
        * https://github.com/stowball/jQuery-rwdImageMaps
        * http://mattstow.com
        * Licensed under the MIT license
        */
        ; (function ($) {
            $.fn.rwdImageMaps = function () {
                var $img = this;

                var rwdImageMap = function () {
                    $img.each(function () {
                        if (typeof ($(this).attr('usemap')) == 'undefined')
                            return;

                        var that = this,
                            $that = $(that);

                        // Since WebKit doesn't know the height until after the image has loaded, perform everything in an onload copy
                        $('<img />').on('load', function () {
                            var attrW = 'width',
                                attrH = 'height',
                                w = $that.attr(attrW),
                                h = $that.attr(attrH);

                            if (!w || !h) {
                                var temp = new Image();
                                temp.src = $that.attr('src');
                                if (!w)
                                    w = temp.width;
                                if (!h)
                                    h = temp.height;
                            }

                            var wPercent = $that.width() / 100,
                                hPercent = $that.height() / 100,
                                map = $that.attr('usemap').replace('#', ''),
                                c = 'coords';

                            $('map[name="' + map + '"]').find('area').each(function () {
                                var $this = $(this);
                                if (!$this.data(c))
                                    $this.data(c, $this.attr(c));

                                var coords = $this.data(c).split(','),
                                    coordsPercent = new Array(coords.length);

                                for (var i = 0; i < coordsPercent.length; ++i) {
                                    if (i % 2 === 0)
                                        coordsPercent[i] = parseInt(((coords[i] / w) * 100) * wPercent);
                                    else
                                        coordsPercent[i] = parseInt(((coords[i] / h) * 100) * hPercent);
                                }
                                $this.attr(c, coordsPercent.toString());
                            });
                        }).attr('src', $that.attr('src'));
                    });
                };
                $(window).resize(rwdImageMap).trigger('resize');

                return this;
            };
        })(jQuery);
    </script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
        }

        img[usemap] {
            width: 90%;
        }

        #loading-box {
            background-color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #E67603;
            width: 300px;
            height: 70px;
            border-radius: 10px;
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.3);
        }

        #loading-container {
            display: flex;
            justify-content: center;
            padding-bottom: 20px;
        }

        #loading-text {
            color: #E67603;
            font-size: 20px;
            margin-left: 20px;
        }

        body p {
            font-size: 30px;
        }

        .ui-tooltip {
            background-color: white;
            color: #E67603;
            border: 3px solid #E67603;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div id="image-container">
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const modelsJ = <?= json_encode($models) ?>;
                const urlParams = new URLSearchParams(window.location.search);
                const begDate = urlParams.get('beg_date');
                const formation = urlParams.get('formation');
                const geojson = <?= json_encode($geojson) ?>;
                const pageKey = <?= json_encode($pageKey) ?>;
                let currentIndex = 0;
                let loadingTextInterval;

                function generateNextImage() {
                    if (currentIndex >= modelsJ.length) {
                        // All models generated
                        const mapElements = document.getElementsByTagName('map');
                        let imageHtml = '';
                        if (mapElements.length > 0) {
                            imageHtml = '<div>';
                            imageHtml += 'A very special thanks to the excellent';
                            imageHtml += ' <a href="https://gplates.org">GPlates</a> and their';
                            imageHtml += ' <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as';
                            imageHtml += ' <a href="https://www.pygmt.org/latest/">PyGMT</a> which work together to create these images.';
                            imageHtml += '</div>';
                        } else {
                            imageHtml = "No available reconstruction image";
                        }
                        $('#image-container').append(imageHtml);
                        clearInterval(loadingTextInterval);
                        $('#loading-box').remove();
                        return;
                    }

                    const model = modelsJ[currentIndex];
                    $.ajax({
                        url: 'makeImageMap.php',
                        method: 'POST',
                        data: { model: model, beg_date: begDate, formation: formation, geojson: geojson, pageKey: pageKey },
                        success: function (data) {
                            // Update the webpage with the generated image
                            $('#image-container').append(data);
                            // const $latestMap = $('#image-container map:last');
                            // $latestMap.on('mouseenter', 'area', function () {
                            //     if(!$(this).data('tooltip')) {
                            //         const coords = $(this).attr('coords').split(',');
                            //         console.log(coords);
                            //         const position = {
                            //             my: "left+" + (parseInt(coords[0]) + 0) + " top+" + coords[1],
                            //             at: "left top"
                            //         };
                            //         $(this).tooltip({
                            //             position: position,
                            //         }).triggerHandler('mouseover');
                            //     }
                            // })
                            // Continue to generate the next image
                            // var areas = document.getElementsByTagName( 'area' );
                            // for( var index = 0; index < areas.length; index++ ) {    
                            //     areas[index].addEventListener( 'mouseover', function () {this.focus();}, false );
                            //     areas[index].addEventListener( 'mouseout', function () {this.blur();}, false );
                            // };
                            $('img[usemap]').rwdImageMaps();
                            currentIndex++;
                            generateNextImage();
                        }
                    });
                }

                // Start generating images
                generateNextImage();

                function animateLoadingText() {
                    const loadingText = document.getElementById("loading-text");
                    const dots = ['.', '..', '...'];
                    let dotIndex = 0;

                    loadingTextInterval = setInterval(function () {
                        loadingText.textContent = "Loading reconstruction" + dots[dotIndex] + " This could take up to a minute. It was a long time ago.";
                        dotIndex = (dotIndex + 1) % dots.length;
                    }, 1000);
                }

                // Call the animation function
                animateLoadingText();
            });
        </script>
    </div>
    <div id="loading-container">
        <div id="loading-box">
            <img src="noun_Earth_2199992.svg" alt="Loading Image" width="50" height="50">
            <p id="loading-text">Loading reconstruction... This could take up to a minute. It was a long time ago.</p>
        </div>
    </div>
</body>

</html>