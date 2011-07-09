// webctimpot javascript support - mainly for treeview
M.mod_webctimport = {
// called by tree view
init_treeview: function(Y, username, path) {
	Y.use('io-base', 'node', 'json', function(Y) {
		//alert('!2: '+username+', '+path);
		var node = Y.one('#treeview_root');
		Y.log('Found #toplist node...');
		M.mod_webctimport.load_node(Y, node, path, '1');
	});
},

load_node: function(Y, node, path, index) {
	node.all('li').remove();
	node.append('<li>Loading...</li>');
	Y.io('get_listing.php?path='+encodeURIComponent(path), {
		on: {
		success: function(id, o, args) {
		//alert('success: '+o.responseText);
		M.mod_webctimport.build_tree(Y, node, o.responseText, index);
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

build_tree: function(Y, node, responseText, index) {
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
		if (item.path!=undefined) {
			var path = 'index='+itemindex+'&type=l&path='+M.mod_webctimport.encode(item.path)+'&title='+M.mod_webctimport.encode(item.title);
			if (item.description!=undefined)
				path = path+'&description='+M.mod_webctimport.encode(item.description);
			node.append('<li><input type="checkbox" name="'+path+'"> '+item.title+'</li>');
			M.mod_webctimport.add_subtree(Y, node, item, itemindex);
		}
		else if (item.webcttype=='URL_TYPE/Default') {
			// link
			var path = 'index='+itemindex+'&type=u&url='+M.mod_webctimport.encode(item.source)+'&title='+M.mod_webctimport.encode(item.title);
			if (item.description!=undefined)
				path = path+'&description='+M.mod_webctimport.encode(item.description);
			node.append('<li><input type="checkbox" name="'+path+'"> '+item.title+'</li>');
		} else {
			// file
			var path = 'index='+itemindex+'&type=f&path='+M.mod_webctimport.encode(item.source)+'&title='+M.mod_webctimport.encode(item.title);
			if (item.description!=undefined)
				path = path+'&description='+M.mod_webctimport.encode(item.description);
			node.append('<li><input type="checkbox" name="'+path+'"> '+item.title+'</li>');
		}
	}
},

encode: function(text) {
	text = encodeURIComponent(text);
	return text.replace(/[.]/g,'%2E');
},

add_subtree: function(Y, node, item, index) {
	var subtree = Y.Node.create('<ul><li><input type="button" value="+"/></li></ul>');
	node.append(subtree);
	var button = subtree.one('li input');
	//alert('button = '+button);
	button.set('onclick', function() {
		//alert('!3');
		M.mod_webctimport.expand(Y, subtree, item.path, index);
	});
},

expand : function(Y, ul, path, index) {
	//alert('expand '+path);
	M.mod_webctimport.load_node(Y, ul, path, index);
}
}