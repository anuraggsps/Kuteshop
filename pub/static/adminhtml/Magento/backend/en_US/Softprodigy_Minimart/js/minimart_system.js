/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
String.prototype.replaceAll = function (find, replace) {
    var str = this;
    return str.replace(new RegExp(find, 'g'), replace);
};
var MinimartSlider = new function () {
    this.buttoncounter = 1;
    this.attachButton = function (Htmlid, url) {
        $(Htmlid).insert({'after': '<button type="button" onclick="MinimartSlider.getList(\'' + Htmlid + '\', \'' + url + '\');"><span>...</span></button><div style="display:none;" class="popup_to_show" id="' + Htmlid + '_popup"><br/><button type="button" id="' + Htmlid + '_applybtn" data-typeval="" onclick="MinimartSlider.applySelected(\'' + Htmlid + '\')"><span>Apply Selected</span></button>&emsp;<button type="button" onclick="MinimartSlider.hideSelected(\'' + Htmlid + '\')"><span>Hide Result</span></button><div id="' + Htmlid + '_popup_container" style="max-height:500px; overflow: auto;"></div></div>'});
    };
    this.getList = function (Htmlid, url) {
        var typeId = Htmlid.replace("_value", "_type");
        var val = $(typeId).value;
        var popup = Htmlid + "_popup";
        $(Htmlid + '_applybtn').setAttribute('data-typeval', val);

        new Ajax.Request(url, {
            method: 'post',
            parameters: {"block_type": val, "popup_id": popup},
            onSuccess: function (transport) {
                // Handle the response content...
                var response = transport.responseText || "";
                $(popup + "_container").innerHTML = response;
                $(popup).show();
                try {
                    if (val == 'product') {
                        executeProductGrid();
                    }
                } catch (e) {
                    console.log(e);
                }
            }
        });
    };
    this.applySelected = function (htmlId) {
        var popup = htmlId + "_popup";
        // console.log(htmlId+'_applybtn');
        // console.log(htmlId+'_applybtn');
        var type = $(htmlId + '_applybtn').getAttribute('data-typeval');
        // alert(type);
        switch (type) {
            case "category":
            case "page":
                $(htmlId).value = $$("input.select_from:checked")[0].value;
                break;
            case "product":
                $(htmlId).value = $$('input[name="product_id"]:checked')[0].value;
                break;
            default:
                $(htmlId).value = $$("input.select_from")[0].value;
                break;
        }
        $(popup + "_container").innerHTML = '';
        $(popup).hide();
    };
    this.hideSelected = function(htmlId){
        var popup = htmlId + "_popup";
        $(popup + "_container").innerHTML = '';
        $(popup).hide();
    };
    
    this.addNewButton = function(template, target,bttonApiLocation){
        var html;
        html = $(template).innerHTML;
        console.log(html);
        var totalRows = $$("#target_buttons_templates tbody tr").length;
        var seq, limit;
        seq = totalRows - 1;
        limit = 4;
        if (seq < limit) {
            html = html.replaceAll("{{input_title_var}}", 'custom_buttons[][title]');
            html = html.replaceAll("{{input_button_type}}", 'custom_buttons[][type]');
            html = html.replaceAll("{{button_type_id}}", 'btn_'+this.buttoncounter+'_type');

            html = html.replaceAll("{{button_val_id}}", 'btn_'+this.buttoncounter+'_value');
            html = html.replaceAll("{{input_button_var}}", 'custom_buttons[][value]');
            $(target).insert(html);
            this.attachButton('btn_'+this.buttoncounter+'_value',bttonApiLocation);
            this.buttoncounter +=1;
        }
    };
};
executeProductGrid = function () {
    var gridId = $('gridId').value;
    var gridUrl = $('gridUrl').value;

    ajaxproductGridJsObject = new varienGrid(gridId, gridUrl, 'page', 'sort', 'dir', 'product_filter');
    ajaxproductGridJsObject.useAjax = '1';
    ajaxproductGridJsObject.rowClickCallback = openGridRow;

};