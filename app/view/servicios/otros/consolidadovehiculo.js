/*
 * File: app/view/servicios/otros/consolidadovehiculo.js
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

Ext.define('siadno.view.servicios.otros.consolidadovehiculo', {
    extend: 'Ext.window.Window',
    alias: 'widget.ServiciosOtrosConsolidadoVehiculo',

    autoShow: true,
    height: 480,
    width: 640,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },
    iconCls: 'icon-application_view_list',
    title: 'Resumen Consolidado por Tipo de Vehiculo',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    margins: '3 3 3 3',
                    height: 45,
                    id: 'formParametros1',
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
                            fieldLabel: 'Fecha Inicial',
                            name: 'fechainicial',
                            submitFormat: 'Y-m-d',
                            listeners: {
                                change: {
                                    fn: me.onDatefieldChange,
                                    scope: me
                                }
                            }
                        },
                        {
                            xtype: 'datefield',
                            flex: 10,
                            margins: '0 3 0 0',
                            fieldLabel: 'Fecha Final',
                            name: 'fechafinal',
                            submitFormat: 'Y-m-d',
                            listeners: {
                                change: {
                                    fn: me.onDatefieldChange1,
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
                    id: 'gridServicios1',
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'tipovehiculo',
                            text: 'Tipo de Vehiculo',
                            flex: 6
                        },
                        {
                            xtype: 'numbercolumn',
                            summaryType: 'sum',
                            align: 'center',
                            dataIndex: 'cantidad',
                            text: 'Cantidad',
                            flex: 4
                        },
                        {
                            xtype: 'numbercolumn',
                            summaryType: 'sum',
                            align: 'right',
                            dataIndex: 'total',
                            text: 'Total',
                            flex: 4
                        },
                        {
                            xtype: 'numbercolumn',
                            summaryType: 'sum',
                            align: 'right',
                            dataIndex: 'p40',
                            text: '40%',
                            flex: 4
                        },
                        {
                            xtype: 'numbercolumn',
                            summaryType: 'sum',
                            align: 'right',
                            dataIndex: 'p60',
                            text: '60%',
                            flex: 4
                        }
                    ],
                    selModel: Ext.create('Ext.selection.RowModel', {

                    }),
                    features: [
                        {
                            ftype: 'summary'
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
                            xtype: 'buttongroup',
                            title: 'Impresión',
                            columns: 3,
                            items: [
                                {
                                    xtype: 'button',
                                    iconCls: 'icon-pdf',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick11,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'button',
                                    iconCls: 'icon-excel',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick21,
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
                                            fn: me.onButtonClickSalir11,
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
                    fn: me.onWindowShow,
                    scope: me
                }
            }
        });

        me.callParent(arguments);
    },

    onDatefieldChange: function(field, newValue, oldValue, eOpts) {
        this.traerServicios();
    },

    onDatefieldChange1: function(field, newValue, oldValue, eOpts) {
        this.traerServicios();
    },

    onButtonClick11: function(button, e, eOpts) {
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
        grid.getStore().getProxy().extraParams.c=Ext.Number.randomInt(0, 1500000000);
        grid.getStore().load({scope:this, callback:callback});
        grid.getStore().getProxy().extraParams.pdf=0;
    },

    onButtonClick21: function(button, e, eOpts) {
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
        grid.getStore().getProxy().extraParams.c=Ext.Number.randomInt(0, 1500000000);
        grid.getStore().load({scope:this, callback:callback});
        grid.getStore().getProxy().extraParams.excel=0;
    },

    onButtonClickSalir11: function(button, e, eOpts) {
        this.close();
    },

    onWindowShow: function(component, eOpts) {
        var clase=Ext.ClassManager.get("siadno.store.resumenvehiculos.servicios");
        if(Ext.isEmpty(clase)){
            Ext.define('siadno.store.resumenvehiculos.servicios', {
                extend: 'Ext.data.Store',

                constructor: function(cfg) {
                    var me = this;
                    cfg = cfg || {};
                    me.callParent([Ext.apply({
                        storeId: Ext.id(),
                        pageSize: 7,
                        proxy: {
                            extraParams:{accion:101},
                            type: 'ajax',
                            url: 'clases/interfaces/InterfazServiciosResumenVehiculo.php',
                            reader: {
                                type: 'json',
                                idProperty: 'tipovehiculo',
                                messageProperty: 'msg',
                                root: 'data'
                            }
                        },
                        fields: [
                        {
                            name: 'tipovehiculo'
                        },
                        {
                            name: 'cantidad'
                        },
                        {
                            name: 'total'
                        },
                        {
                            name: 'p40'
                        },
                        {
                            name: 'p60'
                        }
                        ]
                    }, cfg)]);
                }
            });
        }


        this.fechaInicial=Ext.ComponentQuery.query('ServiciosOtrosConsolidadoVehiculo datefield[name=fechainicial]')[0];
        this.fechaFinal=Ext.ComponentQuery.query('ServiciosOtrosConsolidadoVehiculo datefield[name=fechafinal]')[0];

        this.fechaInicial.suspendEvents(false);
        this.fechaFinal.suspendEvents(false);

        var dt = new Date();
        this.fechaInicial.setValue(dt);
        this.fechaFinal.setValue(dt);


        this.fechaInicial.resumeEvents();
        this.fechaFinal.resumeEvents();

        this.grid=Ext.ComponentQuery.query('ServiciosOtrosConsolidadoVehiculo grid')[0];
        this.grid.reconfigure(Ext.create('siadno.store.resumenvehiculos.servicios'));

        this.traerServicios();
    },

    traerServicios: function() {
        this.grid.getStore().getProxy().extraParams.fechafinal=this.fechaFinal.getSubmitData().fechafinal;
        this.grid.getStore().getProxy().extraParams.fechainicial=this.fechaInicial.getSubmitData().fechainicial;
        this.grid.getStore().load();
    }

});