/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
String.prototype.replaceAll = function (find, replace) {
    var str = this;
    return str.replace(new RegExp(find, 'g'), replace);
};
var minimartNotification = new function () {
    this.preseleceted = {};
    this.addNewBox = function (element) {
        if (element.checked) {
            var value = element.value;
            var html = $("pre_processor_elem").innerHTML;
            var labelText = $("label_" + element.id).innerHTML;
            html = html.replaceAll("{{row_id}}", value);
            html = html.replaceAll("{{customer}}", labelText);
            html = html.replaceAll("{{class_name}}", "fill-text-msg required-entry");
            $("right-col-block").insert(html);
            var req = $('offertypeurl').value;
            MinimartSlider.attachButton('minimart_'+value+'_offer_value', req);
        } else {
            $("secl_ele" + element.value).remove();
        }
    };

    this.applyToAll = function () {
        var text = $("apply_to_all_msg").value;
        var itmtype = $("minimart_all_offer_type").value;
        var itemval =  $("minimart_all_offer_value").value;
        
        $$(".fill-text-msg").each(function (itm) {
            $(itm).value = text;
        });
        
        $$(".custm-offr-itm-type").each(function (itm) {
            $(itm).value = itmtype;
        });
        
        $$(".custm-offr-itm-val").each(function (itm) {
            $(itm).value = itemval;
        });
    };

    this.ArrangeExisting = function () {
        if (Object.keys(this.preseleceted).length) {
            for (var prop in this.preseleceted) {
                var ele = $("customer_row_id_"+prop);
                ele.checked = true;
                this.addNewBox(ele);
                $$(".data-rid-"+prop)[0].value = this.preseleceted[prop];
            }
        }
    };

};