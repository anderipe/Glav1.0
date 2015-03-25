/*
 * File: app/view/servicios/otros/ingresosygastos.js
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

Ext.define('siadno.view.servicios.otros.ingresosygastos', {
    extend: 'Ext.window.Window',
    alias: 'widget.ServiciosOtrosIngresosyGastos',

    height: 600,
    width: 800,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    iconCls: 'icon-layout',
    title: 'Ingresos y Gastos Diarios',

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
                            fieldLabel: 'Día del Registro',
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
                    xtype: 'container',
                    flex: 1,
                    layout: {
                        align: 'stretch',
                        type: 'hbox'
                    },
                    items: [
                        {
                            xtype: 'gridpanel',
                            flex: 1,
                            title: 'Ingresos',
                            columns: [
                                {
                                    xtype: 'gridcolumn',
                                    dataIndex: 'descripcion',
                                    text: 'Descripción',
                                    flex: 5
                                },
                                {
                                    xtype: 'numbercolumn',
                                    summaryType: 'sum',
                                    align: 'right',
                                    dataIndex: 'valor',
                                    text: 'Valor',
                                    flex: 2,
                                    format: '0,000',
                                    editor: {
                                        xtype: 'numberfield',
                                        maxValue: 100000000,
                                        minValue: 0,
                                        step: 5000
                                    }
                                }
                            ],
                            features: [
                                {
                                    ftype: 'summary'
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
                            ]
                        },
                        {
                            xtype: 'gridpanel',
                            flex: 1,
                            title: 'Egresos',
                            columns: [
                                {
                                    xtype: 'gridcolumn',
                                    dataIndex: 'descripcion',
                                    text: 'Descripción',
                                    flex: 5
                                },
                                {
                                    xtype: 'numbercolumn',
                                    summaryType: 'sum',
                                    align: 'right',
                                    dataIndex: 'valor',
                                    text: 'Valor',
                                    flex: 2,
                                    format: '0,000',
                                    editor: {
                                        xtype: 'numberfield',
                                        maxValue: 100000000,
                                        minValue: 0,
                                        step: 5000
                                    }
                                }
                            ],
                            features: [
                                {
                                    ftype: 'summary'
                                }
                            ],
                            plugins: [
                                Ext.create('Ext.grid.plugin.CellEditing', {
                                    listeners: {
                                        beforeedit: {
                                            fn: me.onGridcelleditingpluginBeforeEdit1,
                                            scope: me
                                        },
                                        edit: {
                                            fn: me.onGridcelleditingpluginEdit1,
                                            scope: me
                                        }
                                    }
                                })
                            ]
                        }
                    ]
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    flex: 1,
                    dock: 'bottom',
                    items: [
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
        var idTipoGasto=eOpts.record.get('idtipogasto');
        if(idTipoGasto<=5){
            return false;
        }
        return true;
    },

    onGridcelleditingpluginEdit: function(editor, e, eOpts) {

        var fecha=this.fecha.getSubmitData().fecha;
        var idtipogasto=e.record.get('idtipogasto');
        var valor=e.value;

        var callback=function(){    
            this.traerResumen();
        };

        siadno.ajax.call(this, 'clases/interfaces/InterfazServiciosIngresosGastos.php', {accion:103, idtipogasto:idtipogasto, fecha:fecha, valor:valor}, callback);
    },

    onGridcelleditingpluginBeforeEdit1: function(e, eOpts) {
        var idTipoGasto=eOpts.record.get('idtipogasto');
        if(idTipoGasto<=5){
            return false;
        }
        return true;
    },

    onGridcelleditingpluginEdit1: function(editor, e, eOpts) {
        var fecha=this.fecha.getSubmitData().fecha;
        var idtipogasto=e.record.get('idtipogasto');
        var valor=e.value;

        var callback=function(){    
            this.traerResumen();
        };

        siadno.ajax.call(this, 'clases/interfaces/InterfazServiciosIngresosGastos.php', {accion:103, idtipogasto:idtipogasto, fecha:fecha, valor:valor}, callback);
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

        var clase=Ext.ClassManager.get("siadno.store.servicios.gastos2");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.servicios.gastos2', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        autoLoad: false,
                        storeId: Ext.id(),
                        proxy: {
                            extraParams:{accion:102, gasto:true},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazServiciosIngresosGastos.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idgastodiario',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idgastodiario'
                        },
                        {
                            name: 'idtipogasto'
                        },
                        {
                            name: 'descripcion'
                        },
                        {
                            name: 'valor',
                            type: 'float'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }

        var clase=Ext.ClassManager.get("siadno.store.servicios.ingresos2");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.servicios.ingresos2', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        autoLoad: false,
                        storeId: Ext.id(),
                        proxy: {
                            extraParams:{accion:102, gasto:false},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazServiciosIngresosGastos.php',
                            reader: {
                                type: 'json',
                                idProperty: 'idgastodiario',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'idgastodiario'
                        },
                        {
                            name: 'idtipogasto'
                        },
                        {
                            name: 'descripcion'
                        },
                        {
                            name: 'valor',
                            type: 'float'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }



        this.gridIngreso=Ext.ComponentQuery.query('ServiciosOtrosIngresosyGastos grid')[0];
        this.gridGasto=Ext.ComponentQuery.query('ServiciosOtrosIngresosyGastos grid')[1];
        this.fecha=Ext.ComponentQuery.query('ServiciosOtrosIngresosyGastos datefield')[0];
        this.fecha.suspendEvents(false);

        var dt = new Date();
        this.fecha.setValue(dt);

        this.fecha.resumeEvents();

        this.gridGasto.reconfigure(Ext.create('siadno.store.servicios.gastos2'));
        this.gridIngreso.reconfigure(Ext.create('siadno.store.servicios.ingresos2'));

        this.traerResumen();
    },

    traerResumen: function() {
        this.gridGasto.getStore().getProxy().extraParams.fecha=this.fecha.getSubmitData().fecha;
        this.gridGasto.getStore().load();

        this.gridIngreso.getStore().getProxy().extraParams.fecha=this.fecha.getSubmitData().fecha;
        this.gridIngreso.getStore().load();
    }

});