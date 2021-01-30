package com.clink.ui
{
	import com.clink.factories.Factory_prettyBox;
	import com.clink.utils.DrawingUtils;
	
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.MouseEvent;
	
	/**
	 * Creates a scrolling list of a specified height and width with the specified background color(if any) 
	 * must initialize scrollBars with ScrollBar.initScrollBars(). This has to have been called once before using
	 * @author adamrensel
	 * 
	 */	
	public class SortingScrollList extends Sprite
	{
		private var _itemList:Array;
		private var _sb:ScrollBar;
		private var _mask:Sprite;
		private var _bg:Sprite;
		private var _sortingLB:SortingLayoutBox;
		private var _mainLB:LayoutBox;
		private var _topLB:LayoutBox;
		
		private var _boxHeight:Number;
		private var _boxWidth:Number;
		private var _bgColor:uint;
		
		/**
		 * Creates the scrolling list with given height and width and optional background color 
		 * @param width:Number scrollList width
		 * @param height:Number scrollList Height
		 * @param bgColor:String background color can be passed in as a css value ex. #FFFFFF or as an actionscript hex value 0xFFFFFF, either will work
		 * 
		 */		
		public function SortingScrollList(width:Number,height:Number,bgColor:String = null)
		{
			super();
			
			_boxWidth = width;
			_boxHeight = height;
			
			if(bgColor)
			{
				_bgColor = uint(DrawingUtils.fixColorCode(bgColor));
			}
			
			
			init();
		}
		
		private function init():void
		{
			while(this.numChildren > 0)
			{
				this.removeChildAt(0);
			}
			
			//create the bg if a color is given
			if(_bgColor)
			{
				_bg = Factory_prettyBox.drawPrettyBox(_boxWidth,_boxHeight,_bgColor,0,false,false,true);
				this.addChild(_bg);
			}
			
			//new layoutBoxes
			if(!_mainLB)
			{
				_mainLB = new LayoutBox(true,0);
				_topLB = new LayoutBox(true,0);
				_sortingLB = new SortingLayoutBox(true,0);
				_mainLB.addChild(_topLB);
				_mainLB.addChild(_sortingLB);
			}
			this.addChild(_mainLB);
			
			
			//create and set mask
			_mask = new Sprite();
			_mask.graphics.beginFill(0xffffff,0);
			_mask.graphics.drawRect(0,0,_boxWidth,_boxHeight);
			_mask.graphics.endFill();
			this.addChild(_mask);
			_mainLB.mask = _mask;
			
			//create scrollbar
			_sb = new ScrollBar(_boxHeight,true);
			_sb.x = _boxWidth - _sb.width + 1;
			_sb.y = 0;
			_sb.handleSize = 1;
			
			_sb.addEventListener(Event.CHANGE,onSbChange);
			this.addEventListener(MouseEvent.MOUSE_WHEEL,onMouseWheel);
			update();
		}
		
		//updates the handle size and adds/removes the scrollBar if the list is big enough to need to scroll
		private function updateHandleSize():void
		{
			var perc:Number = _boxHeight/_mainLB.height;

			if(perc >= 1)
			{
				if(_sb.parent == this)
				{
					this.removeChild(_sb);
				}
				
				_sb.handleSize = 1;	
			}else{
				
				if(_sb.parent != this)
				{
					this.addChild(_sb);
				}
				
				if(perc > .15)
				{
					_sb.handleSize = perc;
				}else{
					_sb.handleSize = .15;
				}
			}	
		}
		
		
		/////////////callbacks///////////////
		//mouse wheel scrolling
		private function onMouseWheel(e:MouseEvent):void
		{
			if(_sb.value + (-e.delta / 50) < 0)
			{
				_sb.value = 0;
				
			}else if(_sb.value + (-e.delta / 50) > 1){
				
				_sb.value = 1;
			}else{
				_sb.value += -e.delta / 50;
			}
		}
		
		//when the scroll bar scrolls
		private function onSbChange(e:Event):void
		{
			if(_sb.parent == this)
			{
				_mainLB.y = -Math.round((_mainLB.height - _boxHeight) * _sb.value);
			}else{
				_mainLB.y = 0;
			}
			
		}
		
		////////////public methods///////////
		/**
		 * Updates the scroll list, will need to be called if a list item has changed size, for example if the item is a textField and something
		 * new has been typed in. 
		 * 
		 */		
		public function update():void
		{
			updateHandleSize();
			_sortingLB.updateLayout();
			_topLB.updateLayout();
			_mainLB.updateLayout();
		}
		
		/**
		 * Adds a displayObject to the scrolling list 
		 * @param item:DisplayObject object to add to the list
		 * 
		 */		
		public function addListItem(item:DisplayObject,sortByLabel:String,arraySortSetting:uint = -1):void
		{
			_sortingLB.addItem(item,sortByLabel,arraySortSetting);
			
			updateHandleSize();
		}
		
		
		public function removeListItem(item:DisplayObject,sortByLabel:String):void
		{
			_sortingLB.removeItem(item,sortByLabel);
			
			updateHandleSize();
			
		}
		
		public function addTopItem(item:DisplayObject):void
		{
			_topLB.addChild(item);
			updateHandleSize();
		}
		
		public function removeTopItem(item:DisplayObject):void
		{
			_topLB.removeChild(item);
			updateHandleSize();
		}
		
		/**
		 * setter for setting an array as the list 
		 * @param items:Array array of items to be the list
		 * 
		 */		
		public function set items(items:Array):void
		{
			_sortingLB.items = items;
		}
		
		/**
		 * getter for items in the list, returns an array of the list items
		 * @return Array 
		 * 
		 */		
		public function get items():Array
		{
			return _sortingLB.items;
		}
		
		public function get topItems():Array
		{
			return _topLB.items;
		}
		
		public function set value(value:Number):void
		{
			_sb.value = value;
		}
		
		public function get value():Number
		{
			return _sb.value;
		}
		
		override public function set height(value:Number):void
		{
			_boxHeight = value;
			init();
			
		}
		
		////////////static methods////////////
		
		////////////getters/setters////////////
	}
}