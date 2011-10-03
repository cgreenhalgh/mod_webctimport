// webctimpot javascript support - mainly for treeview
M.mod_webctimport = {
// called by tree view
init_treeview: function(Y, username, path) {
	Y.use('io-base', 'node', 'json', function(Y) {
		//alert('!2: '+username+', '+path);
		var node = Y.one('#treeview_root');
		Y.log('Found #toplist node...');
		M.mod_webctimport.load_node(Y, node, path, '1', 'get_listing.php');
	});
},

//called by user.php (add)
init_grant_treeview: function(Y) {
	Y.use('io-base', 'node', 'json', function(Y) {
		//alert('!2: '+username+', '+path);
		var node = Y.one('#treeview_root');
		Y.log('Found #toplist node...');
		M.mod_webctimport.load_node(Y, node, '/', '1', 'get_context.php');
	});
},

load_node: function(Y, node, path, index, get_listing) {
	node.all('li').remove();
	node.append('<li>Loading...</li>');
	Y.io(get_listing+'?path='+encodeURIComponent(path), {
		on: {
		success: function(id, o, args) {
		//alert('success: '+o.responseText);
		M.mod_webctimport.build_tree(Y, node, o.responseText, index, get_listing);
	},
	failure: function(id, o, args) {
		//alert('failure: '+o+': '+o.statusText);
		node.all('li').remove();
		node.append('<li>Sorry: '+o+': '+o.statusText+'</li>');
		// args might be useful
	}
	}
	});	
},

build_tree: function(Y, node, responseText, index, get_listing) {
	node.all('li').remove();
	//node.append('<li>Debug - response: '+responseText+'</li>');
	var json = Y.JSON.parse(responseText);
	//node.append('<li>Debug - json: '+json+'</li>');
	var error = json.error;
	if (error!=undefined) {
		node.append('<li>Sorry: '+error+'</li>');
		return;
	}
	var list = json.list;
	//node.append('<li>Debug - list: '+list+'</li>');
	var maxlen = new Number(list.length).toString().length;
	for (var i in list) {
		var item = list[i];
		var iname = (new Number(i)+1).toString();
		while (iname.length < maxlen)
			iname = '0'+iname;
		var itemindex = index+'_'+iname;
		var path = 'index='+itemindex;
		var li = '';
		var linkextra = ' target="_blank"';
		if (item.path!=undefined) {
			path = path+'&type=l&path='+M.mod_webctimport.encode(item.path)+'&title='+M.mod_webctimport.encode(item.title);
		}
		else if (item.webcttype=='URL_TYPE/Default') {
			path = path+'&type=u&url='+M.mod_webctimport.encode(item.source)+'&title='+M.mod_webctimport.encode(item.title);
			li = li+' <a href="'+item.source+'"'+linkextra+'>'+item.source+'</a>';
		} 
		else {
			// file
			path = path+'&type=f&path='+M.mod_webctimport.encode(item.source)+'&title='+M.mod_webctimport.encode(item.title);
			li = li+' <a href="read_file.php?path='+encodeURIComponent(item.source)+'"'+linkextra+'>Preview file</a>';
		}
		if (item.description!=undefined && item.description.length>0) {
			path = path+'&description='+M.mod_webctimport.encode(item.description);
			li = li+'<br>'+item.description;
		}
		li = '<li class="mod_webctimport_item"><input class="mod_webctimport_checkbox" type="checkbox" name="'+path+'"> '+item.title+li+'</li>';
		node.append(li);
		if (item.path!=undefined) {
			M.mod_webctimport.add_subtree(Y, node, item, itemindex, get_listing);
		}
	}
},

encode: function(text) {
	text = encodeURIComponent(text);
	return text.replace(/[.]/g,'%2E');
},

add_subtree: function(Y, node, item, index, get_listing) {
	var subtree = Y.Node.create('<ul class="mod_webctimport_list"><li class="mod_webctimport_item"><input class="mod_webctimport_expand" type="button" value="+"/></li></ul>');
	node.append(subtree);
	var button = subtree.one('input');
	//alert('button = '+button);
	button.on('click', function() {
		//alert('!3');
		M.mod_webctimport.expand(Y, subtree, item.path, index, get_listing);
	});
},

expand : function(Y, ul, path, index, get_listing) {
	//alert('expand '+path);
	M.mod_webctimport.load_node(Y, ul, path, index, get_listing);
}
}
