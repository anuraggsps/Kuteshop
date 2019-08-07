/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
String.prototype.replaceAll = function (find, replace) {
    var str = this;
    return str.replace(new RegExp(find, 'g'), replace);
};

var minimartHomedesign;
 
require(['jquery'], function($){
    minimartHomedesign = new function () {
        this.maxlimit = 9;
        this.addNewRow = function () {
            var totalRows = $("#block_category_selection_table tbody tr").length;
            var seq, selname, limit;
            seq = totalRows - 1;
            limit = this.maxlimit;
            if (seq < limit) {
                selname = "category[]";
                var html = $("#block_category_template").html();
                html = html.replaceAll("{{seq}}", seq+1);
                html = html.replaceAll("{{sel_input_name}}", selname);
                $("#block_category_template_tbody").append("<tr>"+html+"</tr>");
            }
        };

        this.removeRow = function (element) {
            $(element).closest('tr').remove();
        };

        this.setMaxLimit = function (limit) {
            this.maxlimit = parseInt(limit);
        }
    };
});