{{-- resources/views/menu/preview.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $menuItem->name }} - {{ $menuItem->restaurant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto p-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-900">{{ $menuItem->name }}</h1>
                <span class="text-2xl font-bold text-green-600">{{ $menuItem->formatted_price }}</span>
            </div>
            
            <!-- Restaurant Info -->
            <div class="text-sm text-gray-600 mb-4">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $menuItem->restaurant->name }}</span>
                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded ml-2">{{ $menuItem->category->name }}</span>
            </div>

            <!-- Image -->
            @if($menuItem->image)
            <div class="mb-4">
                <img src="{{ Storage::url($menuItem->image) }}" alt="{{ $menuItem->name }}" 
                     class="w-full h-48 object-cover rounded-lg">
            </div>
            @endif

            <!-- Description -->
            @if($menuItem->description)
            <p class="text-gray-700 mb-4">{{ $menuItem->description }}</p>
            @endif

            <!-- Info Grid -->
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-600">Preparation Time:</span>
                    <span class="text-gray-900">{{ $menuItem->preparation_time }} minutes</span>
                </div>
                <div>
                    <span class="font-medium text-gray-600">Spice Level:</span>
                    <span class="text-gray-900">{{ $menuItem->spice_level_label }}</span>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="flex gap-2 mt-4">
                @if($menuItem->is_available)
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Available</span>
                @else
                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Not Available</span>
                @endif
                
                @if($menuItem->is_featured)
                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">Featured</span>
                @endif
            </div>
        </div>

        <!-- Allergens -->
        @if($menuItem->allergens && count($menuItem->allergens) > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold mb-3">Allergen Information</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($menuItem->allergens as $allergen)
                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">{{ ucfirst($allergen) }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Options -->
        @if($menuItem->has_options && $menuItem->optionCategories->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Available Options</h3>
            @foreach($menuItem->optionCategories as $category)
            <div class="mb-6 last:mb-0">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-900">{{ $category->name }}</h4>
                    @if($category->pivot->is_required)
                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">Required</span>
                    @endif
                </div>
                
                @if($category->description)
                    <p class="text-sm text-gray-600 mb-3">{{ $category->description }}</p>
                @endif

                <div class="space-y-2">
                    @foreach($category->options as $option)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-gray-900">{{ $option->name }}</span>
                        @if($option->additional_price > 0)
                            <span class="text-green-600 font-medium">+{{ $option->formatted_additional_price }}</span>
                        @elseif($option->additional_price == 0)
                            <span class="text-gray-500">Free</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Back Button -->
        <div class="mt-6 text-center">
            <button onclick="window.close()" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                Close Preview
            </button>
        </div>
    </div>
</body>
</html>