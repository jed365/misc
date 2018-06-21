var pModel = SiebelApp.S_App.GetActiveView().GetActiveApplet().GetPModel();
var PArray = SiebelApp.S_App.GetActiveView().GetActiveApplet().GetPropArray();
var tablecol1 = new Array();
var tablecol2 = new Array();
for (var i=0;i< PArray.length;i++){
    tablecol1.push(PArray[i]);
    tablecol2.push(pModel.Get(PArray[i]));
}
var tableArr = new Array();
for (var i=0;i < PArray.length;i++){
    tableArr.push([tablecol1[i],tablecol2[i]]);
}
console.table(tableArr);