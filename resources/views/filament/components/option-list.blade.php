<div class="space-y-4">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-semibold text-gray-900 mb-2">{{ $category->name }}</h4>
        <p class="text-sm text-gray-600 mb-2">{{ $category->description }}</p>
        <div class="flex gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ $category->type_label }}
            </span>
            @if($category->is_required)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Required
                </span>
            @endif
        </div>
    </div>
    
    @if($options->count() > 0)
        <div class="grid grid-cols-1 gap-3">
            @foreach($options as $option)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h5 class="font-medium text-gray-900">{{ $option->name }}</h5>
                            @if($option->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $option->description }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-medium {{ $option->additional_price > 0 ? 'text-green-600' : ($option->additional_price < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $option->formatted_additional_price }}
                            </span>
                            @if(!$option->is_available)
                                <div class="text-xs text-red-600 mt-1">Unavailable</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">No options available for this category.</p>
        </div>
    @endif
</div>