function requiredFieldValidator(value) {
    var valid = /^\d{0,7}(\.\d{0,2}){0,4}$/.test(value) || value == null || value == undefined || !value.length;
    if (!valid) {
        
    }
     return {
            valid: valid,
            msg: "This is a required field"
        };
    if (value == null || value == undefined || !value.length) {
        return {
            valid: false,
            msg: "This is a required field"
        };
    }
    else {
        return {
            valid: true,
            msg: null
        };
    }
}

if (grid != undefined) grid.destroy();
/* slick grid variables */
var dataView;
var grid;
var data = [];
var columns = [
    {
        id: "shop_name",
        name: "Магазин",
        field: "shop_name",
        minWidth: 200,
        //cannotTriggerInsert: true,
        resizable: false,
//        selectable: false
    },
    {
        id: "available",
        name: "Доступность",
        maxWidth: 130,
        cssClass: "editable-cell text-center _cell-effort-driven",
        field: "available",
        formatter: Slick.Formatters.Checkmark,
        editor: Slick.Editors.Checkbox,
        //cannotTriggerInsert: true,
    },
    {
        id: "price",
        name: "Цена",
        field: "price",
        minWidth: 100,
        cssClass: "editable-cell",
        editor: Slick.Editors.Text,
        validator: requiredFieldValidator,
    },
    {
        id: "rebate",
        name: "Скидка",
        field: "rebate",
        minWidth: 100,
        cssClass: "editable-cell",
        editor: Slick.Editors.Text,
        validator: requiredFieldValidator,
    },
    {
        id: "availability_updated_date",
        name: "Дата обновления",
        field: "availability_updated_date",
        minWidth: 100,
    }

    //					{id: "sel", name: "#", field: "num", behavior: "select", cssClass: "cell-selection", minWidth: 60, cannotTriggerInsert: true, resizable: false, selectable: false },
    //					{id: "title", name: "Title", field: "title", minWidth: 100, cssClass: "cell-title", editor: Slick.Editors.Text, validator: requiredFieldValidator, sortable: true},
    //					{id: "desc", name: "Description", field: "description", minWidth: 260, editor: Slick.Editors.LongText},
    //					{id: "duration", name: "Duration", field: "duration", minWidth: 80, cssClass: "text-center", editor: Slick.Editors.Text, sortable: true},
    //					{id: "%", defaultSortAsc: false, name: "% Complete", field: "percentComplete", width: 120, resizable: false, formatter: Slick.Formatters.PercentCompleteBar, editor: Slick.Editors.PercentComplete, sortable: true},
    //					{id: "start", name: "Start", field: "start", minWidth: 100, cssClass: "text-center", editor: Slick.Editors.Date, sortable: true},
    //					{id: "finish", name: "Finish", field: "finish", minWidth: 100, cssClass: "text-center", editor: Slick.Editors.Date, sortable: true},
    //					{id: "effort-driven", name: "Effort Driven", minWidth: 100, cssClass: "text-center cell-effort-driven", field: "effortDriven", formatter: Slick.Formatters.Checkmark, editor: Slick.Editors.Checkbox, cannotTriggerInsert: true, sortable: true}
];

/* slick grid options */
var options = {
    editable: true,
    enableAddRow: false,
    //enableCellNavigation: true,
    asyncEditorLoading: true,
    forceFitColumns: true,
    topPanelHeight: 30,
    rowHeight: 38,
    autoHeight: true,
};

//				var sortcol = "title";
//				var sortdir = 1;
//				var percentCompleteThreshold = 0;
//				var searchString = "";

$(function () {
    // prepare the data
    data = [{
            'id' : 10,
            'shop_name': '111S',
            'available' : 1,
            'price' : 12,
            'rebate' : '',
            'availability_updated_date' : '16.01.2003'

        }, {
            'id' : 12,
            'shop_name': '222S',
            'available' : 0,
            'price' : 22222222,
            'rebate' : '',
            'availability_updated_date' : '16.01.2013'
        }];

    dataView = new Slick.Data.DataView();
    grid = new Slick.Grid("#sg_large", dataView, columns, options);
    grid.setSelectionModel(new Slick.RowSelectionModel());

    grid.onCellChange.subscribe(function (e, args) {
        //                                            var modifiedCells = {};
        //                                          if (!modifiedCells[args.row]) {
        //                                                modifiedCells[args.row] = {};
        //                                            }
        //                                            modifiedCells[args.row][this.getColumns()[args.cell].id] = "slick-cell-modified";
        //                                            console.log(modifiedCells, 'modifiedCells');
        //                                            this.setCellCssStyles("modified", modifiedCells);
        //dataView.updateItem(args.item.id, args.item);
        //console.log(e);
        //                                                alert(args);
        //        console.log(dataView.getItems(), 'dataView');
        //        console.log(args.grid.getData());
        //console.log(JSON.stringify(args.grid.getData()))
    });

    grid.onBeforeEditCell.subscribe(function (e, args) {
        console.log(args);
        if (args.column.id == 'available') { // Checkbox available
            console.log(args.item);
            if (args.item.available)
                return false;
                e.stopImmediatePropagation();

        }
    });



    dataView.beginUpdate();
    dataView.setItems(data);
    dataView.endUpdate();

    $("#gridContainer").resizable();

    var cols = grid.getColumns();
    grid.setColumns(cols);

})


$(document).ready(function(){
    $('#saveShopStatus').click(function(event) {
        var fieldsAreValid = grid.getEditController().commitCurrentEdit()
        if (!fieldsAreValid)
            return;
        var data = dataView.getItems();
        ajax({
            url :'/admin/products/saveShopStatus/7',
            data : {data : JSON.stringify(data)} ,
            success : function(data) {
                locationHashChanged();
                //                if (data.redirectHash !== undefined) {
                //                    if (location.hash == '#' + data.redirectHash)
                //                    else location.hash = data.redirectHash;
                //                }
            }
        });
        console.log(data, 'data');
    });
});