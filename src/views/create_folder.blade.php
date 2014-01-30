<style>
	.alert-box {
		background-color: #f2dede;
		border-color: #ebccd1;
		color: #b94a48;
		padding: 5px;
		text-align: left;
	}
	
	.btn {
		  display: inline-block;
		  padding: 6px 12px;
		  margin-bottom: 0;
		  font-size: 14px;
		  font-weight: normal;
		  line-height: 1.428571429;
		  text-align: center;
		  white-space: nowrap;
		  vertical-align: middle;
		  cursor: pointer;
		  -webkit-user-select: none;
		     -moz-user-select: none;
		      -ms-user-select: none;
		       -o-user-select: none;
		          user-select: none;
		  background-image: none;
		  border: 1px solid transparent;
		  border-radius: 4px;
		}
		.btn:focus {
		  outline: thin dotted;
		  outline: 5px auto -webkit-focus-ring-color;
		  outline-offset: -2px;
		}
		.btn:hover,
		.btn:focus {
		  color: #333;
		  text-decoration: none;
		}
		.btn:active,
		.btn.active {
		  background-image: none;
		  outline: 0;
		  -webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
		          box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
		}
		
		.btn-success {
		  color: #fff;
		  background-color: #5cb85c;
		  border-color: #4cae4c;
		}
		.btn-success:hover,
		.btn-success:focus,
		.btn-success:active,
		.btn-success.active,
		.open .dropdown-toggle.btn-success {
		  color: #fff;
		  background-color: #47a447;
		  border-color: #398439;
		}
		.btn-success:active,
		.btn-success.active,
		.open .dropdown-toggle.btn-success {
		  background-image: none;
		}
</style>

<div class="alert-box">
	
	{{Form::open()}}
	<p>
		Path: {{$location}}
	</p>
	<p>
		The folder you configured does not exist. Would you now create these?
		{{Form::submit('Ok, let`s do the magic', array('class' => 'btn btn-success'))}}
	</p>
	{{Form::close()}}

</div>