/* 
 * The MIT License
 *
 * Copyright 2016 OEAW/ACDH.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

!function (exports, $, Panel, PanelController, params) {
// Everything here assumes $ === jQuery so ensure this

/**
 * A class for displaying geographical information in a panel
 * Inherits from Panel using prototype inheritance (see below)
 * @constructor
 * @memberOf! corpus_shell
 * @extends Panel
 */
var MapPanel = function (id, type, title, url, position, pinned, zIndex, container, panelController, config) {
    Panel.call(this, id, type, title, url, position, pinned, zIndex, container, panelController, config);
    this.Url = this.Url.replace("x-format=html", "x-format=json");

    this.scanResult;
    this.map;
    this.markers = new OpenLayers.Layer.Markers(this.Id + "_markers");
    this.icon_size = new OpenLayers.Size(20, 20);
    this.icon = new OpenLayers.Icon(params.baseURL + 'scripts/style/img/dot.png',
            this.icon_size,
            new OpenLayers.Pixel(-(this.icon_size.w / 2),
                    -(this.icon_size.h / 2))
            );

    /**
     * @param -
     * purpose:    loads this.Url via AJAX and places the content of the remote file
     *             inside the searchresult div; afterwards initializes/refreshes the
     *             scrollbar
     * @return    -
     */
    this.GetFullText = function()
    {
        var elem = this.GetCssId();
        var panel = this;

        $.ajax(
                {
                    type: 'GET',
                    url: this.Url,
                    dataType: 'json',
                    complete: function(xml, textStatus)
                    {
                        panel.scanResult = $.parseJSON(xml.responseText);
                        var responseText = "<div id='" + panel.Id + "_map' class='content' style='width: 100%; height:100%;'/>"

//          responseText = $(responseText).find(".title, .data-view, .navigation, .content");

                        $(elem).find(".searchresults").html(responseText);
                        panel.initalizeLayers();
                    },
                    success: function() {
                        panel.SetUrl(panel.Url);
                    }
                }
        );
    };

    this.initalizeLayers = function() {
        this.map = new OpenLayers.Map(this.Id + "_map", {
            projection: 'EPSG:3857',
            layers: [
                new OpenLayers.Layer.Google(
                        "Google Physical",
                        {type: "terrain"}
                ),
//           new OpenLayers.Layer.OSM("OSM"),
                /*            new OpenLayers.Layer.Google(
                 "Google Streets", // the default
                 {numZoomLevels: 20}
                 ),
                 new OpenLayers.Layer.Google(
                 "Google Hybrid",
                 {type: google.maps.MapTypeId.HYBRID, numZoomLevels: 20}
                 ),
                 new OpenLayers.Layer.Google(
                 "Google Satellite",
                 {type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22}
                 ),*/
                this.markers,
            ],
            controls: [
                new OpenLayers.Control.Attribution(),
                new OpenLayers.Control.DragPan({'autoActivate': true}),
            ],
            center: new OpenLayers.LonLat(16.04, 24.397)
                    // Google.v3 uses web mercator as projection, so we have to
                    // transform our coordinates
                    .transform('EPSG:4326', 'EPSG:3857'),
            zoom: 4,
        });
        this.scanResult.terms.forEach(function(term)
        {
            var LatLon = term.value.split(", ");
            var newIcon = this.icon.clone();
            newIcon.imageDiv.className += " cursor_pointer";
            var marker = new OpenLayers.Marker(new OpenLayers.LonLat(LatLon[1], LatLon[0]).transform(
                    'EPSG:4326', // transform from WGS 1984
                    this.map.getProjectionObject() // to whatever map needs, most of the time Spherical Mercator Projection
                    ), newIcon);
            marker.URL = term.nextHref;
            marker.events.register('click', marker, markerCallback);
            this.markers.addMarker(marker);
        }, this);
    };
    
    var self = this;

    markerCallback = function(evt) {
        var target = this.URL;
        var urlParams = self.GetUrlParams(target);
        var ID = PanelController.OpenNewSearchPanel(urlParams['x-context'], urlParams.query);
        PanelController.StartSearch(ID);
    };

    /**
     * This method is not needed by this type of panel.
     * @returns -
     */
    this.InitScrollPane = function() {
    };
    /**
     * This method needs to pass the call to map's updateSize.
     * @returns -
     */
    this.UpdateContentView = function() {
        this.map.updateSize();
    };

};

MapPanel.prototype = new Panel();
exports.MapPanel = MapPanel;

}(window, jQuery, Panel, PanelController, params); // OpenLayers too, my be loaded by another module / drupal theme