//Javascript for the mass post page
//

//BEGIN POPULATE

  var empty  = null
  //var numSel = 100
 
  function AddRow() {
    var table = document.getElementById( 'MyTable' )
    var last  = null
    if ( table ) {
      var tbody = table.getElementsByTagName( 'TBODY' )[ 0 ]
      last = empty.cloneNode( true )
      tbody.appendChild( last )
    } else {
      //alert( 'Required element not found: "MyTable"' )
    }
    return last
  }
 
  function populate( sel, id ) {
    var table = document.getElementById( id )
    if ( table ) {
      var SI = sel.selectedIndex
      //setCookie( sel.id, SI )
      var val = sel.options[ SI ].value
      var tbody = table.getElementsByTagName( 'TBODY' )[ 0 ]
      if ( val < tbody.rows.length ) {
        if ( !confirm( 'Are you sure?' ) ) {
          return
        }
      }
      while ( val < tbody.rows.length ) {
        tbody.removeChild( tbody.rows[ tbody.rows.length - 1 ] )
      }
      for ( var i = tbody.rows.length; i < val; i++ ) {
        var row = AddRow()
        var inp = []
        var kids = ( 'children' in row ) ? row.children : row.childNodes
	 var epoffset = (document.getElementById('ep_offset').value*1);
        for ( var j = 0; j < kids.length; j++ ) {
          if ( kids[ j ].nodeName == 'TD' ) {
            var eoc = inp[ inp.length ] = kids[ j ].firstChild
            if ( eoc.nodeName == 'INPUT' || eoc.nodeName == 'SELECT' ) {
              if ( eoc.id == 'EOC' ) {
                eoc.name = eoc.id += ( i + 1 )
              } else if ( eoc.id == 'PLAYER' ) {
                eoc.name = eoc.id += ( i + 1 )
              } else if ( eoc.id == 'EP' ) {
                eoc.value = ( i + epoffset )
		  eoc.name = eoc.id += ( i + 1 )
              }
            } else if ( eoc.nodeName == 'TEXTAREA' ) {
		if ( eoc.id == 'EOC' ) {
		   eoc.name = eoc.id += (i + 1)
		}
	    } else {
              alert( 'Unexpected nodeName.  Expected = "INPUT"  Actual = "' + eoc.nodeName + '"' )
            }
          }
        }
      }
    } else {
      //alert( 'Required element not found: "' + id + '"' )
    }
  }
 
// ...
 
  window.onload = function() {
    var table = document.getElementById( 'MyTable' )
    var sel   = document.getElementById( 'MySel' )
    if ( table ) {
      var tbody = table.getElementsByTagName( 'TBODY' )[ 0 ]
      empty = tbody.rows[ tbody.rows.length - 1 ].cloneNode( true )
      tbody.removeChild( tbody.rows[ tbody.rows.length - 1 ] )
    } else {
      //alert( 'Required element not found: "MyTable"' )
    }
    if ( sel ) {
      for ( var i = 0; i < numSel; i++ ) {
        sel.options[ i + 1 ] = new Option( i + 1, i + 1 )
      }
      //var SI = getCookie( 'MySel' )
      //SI = ( SI == '' ) ? 0 : parseInt( SI )
      //sel.selectedIndex = SI
      populate( sel ,"MyTable")
    } else {
      //alert( 'Required element not found: "MySel"' )
    }
  }


// Begin Validation


function field( id ) {
  var ele = document.getElementById( id )
  if ( !ele ) {
    alert( 'Specified element not found.  id="' + id + '"' )
  }
  return ele
}
 
function trim( str ) {
  return str.replace( /^\s\s*/, '' ).replace( /\s\s*$/, '' )
}
 
function validate() {
  // anime-name: 1 or more characters
  var id = 'anime-name';  var what = field( id )
  if ( !what || !trim( what.value ).length ) {
    alert( 'Required field: ' + id + ' ' +( ( what ) ? 'empty' : 'not found.' ) )
    return false
  }
 
  // subdub: 1 radio button must be selected
  id = 'subdub';  what = field( id )
  if ( what ) {
    if ( what.type == 'radio' ) {
      var radio = document.getElementsByName( id )
      for ( var i = 0; i < radio.length; i++ ) {
        if ( radio[ i ].checked )
          break
      }
      if ( i == radio.length ) {
        alert( 'Required field: ' + id + ' no value selected.' )
        return false
      }
    } else {
      alert( 'Required field: ' + id + ' wrong type: ' + what.type )
      return false
    }
  } else {
    alert( 'Required field: ' + id + ' not found.' )
    return false
  }
 
  // MySel: value must be selected.  Confirm if too few
  id = 'MySel';  what = field( id )
  var SI = -1
  if ( what ) {
    if ( what.nodeName == 'SELECT' ) {
      SI = what.selectedIndex
      if ( SI > 0 ) {
        if ( SI < 100 ) {
          if ( !confirm( 'Are you sure you are ready? ' + SI + ' posts in the queue.' ) ) {
            return false
          }
        }
      } else {
        alert( 'Required field: ' + id + ' no selection made.' )
        return false
      }
    } else {
      alert( 'Required field: ' + id + ' unexpected type: ' + what.nodeName )
      return false
    }
  } else {
    alert( 'Required field: ' + id + ' not found.' )
    return false
  }
  
  // EOC: check for non-empty
  for ( var i = 0; i < SI; i++ ) {
    id = 'EOC' + ( i + 1 )
    what = field( id )
    if ( what ) {
      if ( trim( what.value ).length == 0 ) {
        alert( 'Empty field: ' + id )
        return false
      }
    } else {
      return false
    }
  }

  id = 'ep_offset';  what = field( id )
  if ( what ) {
    if ( isNaN(what.value) ) {
      alert( "Starting # is not a number, please fix this" )
      return false
    } else if ( what.value < 1 ) {
      alert( "Starting # must be (1) or more, please fix this" )
      return false
    } else if ( parseInt(what.value)!= what.value ) {
      alert( "Starting # must be a whole number, please fix this" )
      return false
    }
  } else {
    alert( 'Required field: ' + id + ' not found.' )
    return false
  }

  return true
}

function updateNumbers() {
  //document.getElementById('id');
  var offset = (document.getElementById('ep_offset').value*1)
  var number = (document.getElementById('MySel').value*1)
  if (isNaN(offset)) {
    alert( "Starting # is not a number, please fix this" )
  } else if (offset < 1) {
    alert('You must begin on Episode 1 or above')
    document.getElementById('ep_offset').value = 1
  } else if (offset != Math.round(offset)) {
    alert('You must use whole numbers')
    document.getElementById('ep_offset').value = Math.round( offset )
  }
  var ep = 0
  for (i=1; i <= number; i++) {
    ep = document.getElementById('EP'+i)
    if ( ep ){
      //document.getElementById('EP'+i).value = (i + offset);
      ep.value = (i + offset - 1)
    }
  }
}

//Expand
function expandTextBox(object){
	object.style.height = '300px';
	object.style.width = '1070px';
}

//Restore
function restoreTextBox(object){
	object.style.height = '23px';
	object.style.width = '1070px';
}