/*
 * File: app/view/servicios/otros/resumendiario.js
 *
 * This file was generated by Sencha Architect version 2.2.2.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 4.0.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 4.0.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('siadno.view.servicios.otros.resumendiario', {
    extend: 'Ext.window.Window',
    alias: 'widget.ServiciosOtrosResumenDiario',

    height: 600,
    width: 800,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    iconCls: 'icon-layout',
    title: 'Operación Diaria',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    flex: 1,
                    margins: '3 3 3 3',
                    height: 65,
                    maxHeight: 65,
                    layout: {
                        align: 'stretch',
                        type: 'hbox'
                    },
                    bodyPadding: 10,
                    url: 'clases/interfaces/InterfazRegistroServicios.php',
                    items: [
                        {
                            xtype: 'datefield',
                            flex: 10,
                            margins: '0 3 0 0',
                            fieldLabel: 'Día del Informe',
                            labelAlign: 'top',
                            name: 'fecha',
                            format: 'Y/m/d',
                            submitFormat: 'Y-m-d',
                            listeners: {
                                change: {
                                    fn: me.onDatefieldChange,
                                    scope: me
                                }
                            }
                        }
                    ]
                },
                {
                    xtype: 'gridpanel',
                    flex: 1,
                    margins: '3 3 3 3',
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return '<div><b>'+value+'</b></div>';
                            },
                            width: 150,
                            dataIndex: 'd0'
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd1',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd2',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd3',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd4',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd5',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd6',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd7',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd8',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd9',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd10',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd11',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd12',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd13',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd14',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd15',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd16',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd17',
                            editor: {
                                xtype: 'numberfield',
                                width: 100,
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd18',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd19',
                            editor: {
                                xtype: 'numberfield',
                                allowDecimals: false,
                                decimalPrecision: 0,
                                maxValue: 1000000,
                                minValue: 0,
                                step: 5000
                            }
                        },
                        {
                            xtype: 'gridcolumn',
                            renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                return store.miRender(rowIndex, value);
                            },
                            dataIndex: 'd20'
                        }
                    ],
                    plugins: [
                        Ext.create('Ext.grid.plugin.CellEditing', {
                            listeners: {
                                beforeedit: {
                                    fn: me.onGridcelleditingpluginBeforeEdit,
                                    scope: me
                                },
                                edit: {
                                    fn: me.onGridcelleditingpluginEdit,
                                    scope: me
                                }
                            }
                        })
                    ],
                    selModel: Ext.create('Ext.selection.CellModel', {

                    })
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    flex: 1,
                    dock: 'bottom',
                    items: [
                        {
                            xtype: 'buttongroup',
                            title: 'Impresión',
                            columns: 3,
                            items: [
                                {
                                    xtype: 'button',
                                    iconCls: 'icon-pdf',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick1,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'button',
                                    iconCls: 'icon-excel',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick2,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'tbfill'
                        },
                        {
                            xtype: 'buttongroup',
                            columns: 1,
                            layout: {
                                columns: 1,
                                type: 'table'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    iconCls: 'icon-door_out',
                                    text: 'Salir',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClickSalir1,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                }
            ],
            listeners: {
                show: {
                    fn: me.onPanelShow,
                    scope: me
                }
            }
        });

        me.callParent(arguments);
    },

    onDatefieldChange: function(field, newValue, oldValue, eOpts) {
        this.traerResumen();
    },

    onGridcelleditingpluginBeforeEdit: function(e, eOpts) {
        if(eOpts.colIdx===0 || eOpts.colIdx==(e.grid.storeEmpleados.count()+1) || (eOpts.rowIdx!=3 && eOpts.rowIdx!=4 && eOpts.rowIdx!=6)){
            return false;
        }



        return true;

    },

    onGridcelleditingpluginEdit: function(editor, e, eOpts) {
        var idEmpleado=e.grid.storeEmpleados.getAt(e.colIdx-1).get('idempleado');
        var fecha=this.fecha.getSubmitData().fecha;

        var valor=e.value;
        var campo=e.rowIdx;

        var callback=function(){    
            this.traerResumen();
        };

        siadno.ajax.call(this, 'clases/interfaces/InterfazServiciosResumenDiario.php', {accion:103, idempleado:idEmpleado, fecha:fecha, valor:valor, campo:campo}, callback);

    },

    onButtonClick1: function(button, e, eOpts) {
        var grid=this.grid

        var callback=function(records, operation, succes){
            try{                    
                var result = Ext.decode(operation.response.responseText);        
                siadno.descargarPostConsulta(result);
            }catch(e){            
                if(!Ext.isEmpty(e.message)){
                    Ext.MessageBox.alert('Error codificar la respuesta', e.message);
                }else{
                    Ext.MessageBox.alert('Error codificar la respuesta', e);
                }        
            }
        }

        grid.getStore().getProxy().extraParams.pdf=1;
        grid.getStore().load({scope:this, callback:callback});
        grid.getStore().getProxy().extraParams.pdf=0;
    },

    onButtonClick2: function(button, e, eOpts) {
        var grid=this.grid

        var callback=function(records, operation, succes){
            try{                    
                var result = Ext.decode(operation.response.responseText);        
                siadno.descargarPostConsulta(result);
            }catch(e){            
                if(!Ext.isEmpty(e.message)){
                    Ext.MessageBox.alert('Error codificar la respuesta', e.message);
                }else{
                    Ext.MessageBox.alert('Error codificar la respuesta', e);
                }        
            }
        }

        grid.getStore().getProxy().extraParams.excel=1;
        grid.getStore().load({scope:this, callback:callback});
        grid.getStore().getProxy().extraParams.excel=0;
    },

    onButtonClickSalir1: function(button, e, eOpts) {
        this.close();
    },

    onPanelShow: function(component, eOpts) {
        this.miRender=function(rowIndex, value){
            var  valor=Ext.util.Format.number(value, '0,000');

            if(rowIndex==3 || rowIndex==4){
                return "<div style='color:red; text-align:right'>-$"+valor+"</div>";
            }

            if(rowIndex==6){
                return "<div style='color:red; text-align:right'>-$"+valor+"</div>";
            }

            return "<div style='text-align:right'>$"+valor+"</div>";
        };

        var clase=Ext.ClassManager.get("siadno.store.servicios.listaempleados");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.servicios.listaempleados', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        autoLoad: false,
                        storeId: Ext.id(),
                        proxy: {
                            extraParams:{accion:101},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazServiciosResumenDiario.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idempleado',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idempleado'
                        },
                        {
                            name: 'nombres'
                        },
                        {
                            name: 'abreviatura'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        clase=Ext.ClassManager.get("siadno.store.servicios.datosdiarios");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.servicios.datosdiarios', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        autoLoad: false,
                        storeId: Ext.id(),
                        proxy: {
                            extraParams:{accion:102},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazServiciosResumenDiario.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idempleado',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'd0'
                        },
                        {
                            name: 'd1'
                        },
                        {
                            name: 'd2'
                        },
                        {
                            name: 'd3'
                        },
                        {
                            name: 'd4'
                        },
                        {
                            name: 'd5'
                        },
                        {
                            name: 'd6'
                        },
                        {
                            name: 'd7'
                        },
                        {
                            name: 'd8'
                        },
                        {
                            name: 'd9'
                        },
                        {
                            name: 'd10'
                        },
                        {
                            name: 'd11'
                        },
                        {
                            name: 'd12'
                        },
                        {
                            name: 'd13'
                        },
                        {
                            name: 'd14'
                        },
                        {
                            name: 'd15'
                        },
                        {
                            name: 'd16'
                        },
                        {
                            name: 'd17'
                        },
                        {
                            name: 'd18'
                        },
                        {
                            name: 'd19'
                        },
                        {
                            name: 'd20'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }


        this.grid=Ext.ComponentQuery.query('ServiciosOtrosResumenDiario grid')[0];
        this.fecha=Ext.ComponentQuery.query('ServiciosOtrosResumenDiario datefield')[0];
        this.fecha.suspendEvents(false);

        var dt = new Date();
        this.fecha.setValue(dt);

        this.fecha.resumeEvents();


        //alert(this.grid.columns);
        this.storeEmpleados=Ext.create('siadno.store.servicios.listaempleados');
        this.storeEmpleados.load({
            scope: this,
            callback: function(records, operation, success) {
                var numEmpleados=this.storeEmpleados.count();
                var numColumnas=this.grid.columns.length;        

                for(var i=0; i<numColumnas; i++){            
                    var columna=this.grid.columns[i];            

                    if(i==0){                                  
                        columna.setText('Concepto');
                        columna.updateLayout();                
                    }else{
                        if(i==numEmpleados+1){                     
                            columna.setText('Totales');
                            columna.updateLayout();                
                        }else{
                            if(i<numEmpleados+1){
                                var registro=this.storeEmpleados.getAt(i-1);                        
                                columna.setText(registro.get('abreviatura'));
                                columna.updateLayout();  
                            }
                        }
                    }



                    columna.setVisible(true);
                    if(i>numEmpleados+1){
                        columna.setVisible(false);                                
                    }
                };

                this.grid.reconfigure(Ext.create('siadno.store.servicios.datosdiarios'));
                this.grid.getStore().miRender=this.miRender;
                this.grid.storeEmpleados=this.storeEmpleados;            
                this.grid.traerResumen=this.traerResumen;        
                this.traerResumen();
            }
        });

    },

    traerResumen: function() {
        this.grid.getStore().getProxy().extraParams.fecha=this.fecha.getSubmitData().fecha;
        this.grid.getStore().getProxy().extraParams.accion=102;
        this.grid.getStore().load();
    }

});